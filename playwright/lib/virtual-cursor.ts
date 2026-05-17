import type { Locator, Page } from '@playwright/test';
import { tourMs } from './tour-speed.js';

const CURSOR_ID = 'pw-tour-cursor';
const RIPPLE_ID = 'pw-tour-cursor-ripple';
const RIPPLE_ANIM_MS = tourMs(450);

/** 各ページ読み込み時にカーソル DOM を差し込む */
const CURSOR_INIT = `
(() => {
  if (document.getElementById('${CURSOR_ID}')) return;

  const style = document.createElement('style');
  style.textContent = \`
    #${CURSOR_ID} {
      position: fixed;
      left: 0;
      top: 0;
      width: 28px;
      height: 28px;
      margin: -4px 0 0 -4px;
      pointer-events: none;
      z-index: 2147483647;
      transform: translate(var(--cx, 50vw), var(--cy, 50vh));
      filter: drop-shadow(0 1px 2px rgba(0,0,0,0.35));
      transition: none;
    }
    #${CURSOR_ID}.is-clicking {
      transform: translate(var(--cx), var(--cy)) scale(0.88);
    }
    #${CURSOR_ID} svg { display: block; }
    #${RIPPLE_ID} {
      position: fixed;
      left: 0;
      top: 0;
      width: 48px;
      height: 48px;
      margin: -24px 0 0 -24px;
      pointer-events: none;
      z-index: 2147483646;
      border-radius: 50%;
      border: 2px solid rgba(60, 120, 255, 0.7);
      transform: translate(var(--cx), var(--cy)) scale(0.2);
      opacity: 0;
      transition: none;
    }
    #${RIPPLE_ID}.is-active {
      animation: pw-tour-ripple ${RIPPLE_ANIM_MS}ms ease-out forwards;
    }
    @keyframes pw-tour-ripple {
      0% { opacity: 0.85; transform: translate(var(--cx), var(--cy)) scale(0.2); }
      100% { opacity: 0; transform: translate(var(--cx), var(--cy)) scale(1.6); }
    }
  \`;

  const cursor = document.createElement('div');
  cursor.id = '${CURSOR_ID}';
  cursor.innerHTML = \`<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28" aria-hidden="true">
    <path fill="#fff" stroke="#1a1a1a" stroke-width="1.2" d="M5 3 L5 22 L10 17 L14 25 L17 23 L13 15 L20 15 Z"/>
  </svg>\`;

  const ripple = document.createElement('div');
  ripple.id = '${RIPPLE_ID}';

  const mount = () => {
    document.documentElement.appendChild(style);
    document.body.appendChild(ripple);
    document.body.appendChild(cursor);
    const cx = window.innerWidth * 0.5;
    const cy = window.innerHeight * 0.45;
    cursor.style.setProperty('--cx', cx + 'px');
    cursor.style.setProperty('--cy', cy + 'px');
    ripple.style.setProperty('--cx', cx + 'px');
    ripple.style.setProperty('--cy', cy + 'px');
    window.__pwCursorPos = { x: cx, y: cy };
  };

  if (document.body) mount();
  else document.addEventListener('DOMContentLoaded', mount, { once: true });
})();
`;

type Point = { x: number; y: number };

declare global {
  interface Window {
    __pwCursorPos?: Point;
  }
}

function cubicBezier(t: number, p0: number, p1: number, p2: number, p3: number): number {
  const u = 1 - t;
  return u * u * u * p0 + 3 * u * u * t * p1 + 3 * u * t * t * p2 + t * t * t * p3;
}

function easeInOutQuad(t: number): number {
  return t < 0.5 ? 2 * t * t : 1 - Math.pow(-2 * t + 2, 2) / 2;
}

function easeInOutCubic(t: number): number {
  return t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;
}

export async function installVirtualCursor(page: Page): Promise<void> {
  await page.addInitScript(CURSOR_INIT);
  await page.evaluate(CURSOR_INIT);
  const pos = await page.evaluate(() => window.__pwCursorPos!);
  await page.mouse.move(pos.x, pos.y);
}

/**
 * 実マウス（ホバー・クリック）と録画用の擬似カーソルを常に同じ座標に揃える。
 * DOM だけ動かすとホバーが別の場所で反応してしまう。
 */
async function syncPointer(page: Page, x: number, y: number): Promise<void> {
  await page.mouse.move(x, y);
  await page.evaluate(
    ({ x, y, cursorId, rippleId }) => {
      const px = `${x}px`;
      const py = `${y}px`;
      document.getElementById(cursorId)?.style.setProperty('--cx', px);
      document.getElementById(cursorId)?.style.setProperty('--cy', py);
      document.getElementById(rippleId)?.style.setProperty('--cx', px);
      document.getElementById(rippleId)?.style.setProperty('--cy', py);
      window.__pwCursorPos = { x, y };
    },
    { x, y, cursorId: CURSOR_ID, rippleId: RIPPLE_ID },
  );
}

async function getPointerPosition(page: Page): Promise<Point> {
  return page.evaluate(() => {
    const pos = window.__pwCursorPos;
    return pos ?? { x: window.innerWidth * 0.5, y: window.innerHeight * 0.45 };
  });
}

async function playClickEffect(page: Page): Promise<void> {
  await page.evaluate(
    ({ cursorId, rippleId }) => {
      const cursor = document.getElementById(cursorId);
      const ripple = document.getElementById(rippleId);
      cursor?.classList.add('is-clicking');
      ripple?.classList.remove('is-active');
      void ripple?.offsetWidth;
      ripple?.classList.add('is-active');
    },
    { cursorId: CURSOR_ID, rippleId: RIPPLE_ID },
  );
  await page.waitForTimeout(tourMs(100));
  await page.evaluate((cursorId) => {
    document.getElementById(cursorId)?.classList.remove('is-clicking');
  }, CURSOR_ID);
}

/** 要素の中心へ、実マウスと擬似カーソルを同じ軌道で移動 */
export async function moveVirtualCursor(
  page: Page,
  toX: number,
  toY: number,
  options: { durationMs?: number } = {},
): Promise<void> {
  const { durationMs = tourMs(750) } = options;
  const from = await getPointerPosition(page);

  const spread = Math.min(120, Math.hypot(toX - from.x, toY - from.y) * 0.35);
  const cp1x = from.x + (toX - from.x) * 0.28 + (Math.random() - 0.5) * spread;
  const cp1y = from.y + (toY - from.y) * 0.15 + (Math.random() - 0.5) * spread * 0.6;
  const cp2x = from.x + (toX - from.x) * 0.72 + (Math.random() - 0.5) * spread;
  const cp2y = from.y + (toY - from.y) * 0.85 + (Math.random() - 0.5) * spread * 0.6;

  const steps = Math.max(20, Math.round(durationMs / 14));
  const stepDelay = durationMs / steps;

  for (let i = 1; i <= steps; i++) {
    const t = easeInOutQuad(i / steps);
    const x = cubicBezier(t, from.x, cp1x, cp2x, toX);
    const y = cubicBezier(t, from.y, cp1y, cp2y, toY);
    await syncPointer(page, x, y);
    if (i < steps) {
      await page.waitForTimeout(stepDelay);
    }
  }
}

/** ページをスクロールしながら、カーソルはクリック先（ナビ）へ向ける */
export async function smoothScrollAndMoveTo(
  page: Page,
  targetX: number,
  targetY: number,
  options: { durationMs?: number } = {},
): Promise<void> {
  const { durationMs = tourMs(533) } = options;
  const from = await getPointerPosition(page);
  const { startScroll, targetScroll } = await page.evaluate((ty) => {
    const startScroll = window.scrollY;
    const targetScroll = Math.max(
      0,
      Math.min(startScroll, ty - window.innerHeight * 0.32),
    );
    return { startScroll, targetScroll };
  }, targetY);

  const steps = Math.max(24, Math.round(durationMs / 16));

  for (let i = 1; i <= steps; i++) {
    const t = easeInOutCubic(i / steps);
    const x = from.x + (targetX - from.x) * t;
    const y = from.y + (targetY - from.y) * t;
    const scrollY = startScroll + (targetScroll - startScroll) * t;
    await page.evaluate((sy) => window.scrollTo(0, sy), scrollY);
    await syncPointer(page, x, y);
    if (i < steps) {
      await page.waitForTimeout(tourMs(16));
    }
  }
}

export async function humanNavClick(page: Page, locator: Locator): Promise<void> {
  await locator.waitFor({ state: 'visible' });

  let box = await locator.boundingBox();
  if (!box) {
    throw new Error('humanNavClick: 要素の bounding box を取得できませんでした');
  }

  let x = box.x + box.width / 2;
  let y = box.y + box.height / 2;
  const urlBefore = page.url();

  await smoothScrollAndMoveTo(page, x, y, { durationMs: tourMs(533) });
  await page.waitForTimeout(tourMs(350));

  box = await locator.boundingBox();
  if (!box) {
    throw new Error('humanNavClick: スクロール後に bounding box を取得できませんでした');
  }
  x = box.x + box.width / 2;
  y = box.y + box.height / 2;
  await syncPointer(page, x, y);

  await playClickEffect(page);
  await page.waitForTimeout(tourMs(300));

  await Promise.all([
    page.waitForURL((url) => url.href !== urlBefore, { timeout: 60_000 }),
    page.mouse.click(x, y),
  ]);

  await page.waitForLoadState('domcontentloaded');
}

/** 遷移後: 本文の見出し付近へ移動（要素の実座標に合わせる） */
export async function moveCursorToMainContent(page: Page): Promise<void> {
  const heading = page.locator('main h1, main h2').first();
  const target = (await heading.count()) > 0 ? heading : page.locator('main');
  await target.scrollIntoViewIfNeeded();
  const box = await target.boundingBox();
  if (!box) {
    return;
  }
  await moveVirtualCursor(page, box.x + box.width / 2, box.y + Math.min(box.height * 0.5, 80), {
    durationMs: tourMs(650),
  });
  await page.waitForTimeout(tourMs(300));
}

/** ページ内リンクをクリック（カーソル移動 → 遷移待ち） */
export async function humanLinkClick(page: Page, locator: Locator): Promise<void> {
  await locator.waitFor({ state: 'visible' });
  await locator.scrollIntoViewIfNeeded();

  let box = await locator.boundingBox();
  if (!box) {
    throw new Error('humanLinkClick: 要素の bounding box を取得できませんでした');
  }

  let x = box.x + box.width / 2;
  let y = box.y + box.height / 2;
  const urlBefore = page.url();
  const from = await getPointerPosition(page);
  const dist = Math.hypot(x - from.x, y - from.y);

  await moveVirtualCursor(page, x, y, {
    durationMs: tourMs(Math.min(1200, Math.max(450, dist * 0.55))),
  });
  await page.waitForTimeout(tourMs(350));

  box = await locator.boundingBox();
  if (!box) {
    throw new Error('humanLinkClick: 移動後に bounding box を取得できませんでした');
  }
  x = box.x + box.width / 2;
  y = box.y + box.height / 2;
  await syncPointer(page, x, y);

  await playClickEffect(page);
  await page.waitForTimeout(tourMs(300));

  await Promise.all([
    page.waitForURL((url) => url.href !== urlBefore, { timeout: 60_000 }),
    page.mouse.click(x, y),
  ]);

  await page.waitForLoadState('domcontentloaded');
}

export async function humanClick(page: Page, locator: Locator): Promise<void> {
  await locator.scrollIntoViewIfNeeded();
  const box = await locator.boundingBox();
  if (!box) {
    throw new Error('humanClick: 要素の bounding box を取得できませんでした');
  }

  const x = box.x + box.width / 2;
  const y = box.y + box.height / 2;
  const from = await getPointerPosition(page);
  const dist = Math.hypot(x - from.x, y - from.y);

  await moveVirtualCursor(page, x, y, {
    durationMs: tourMs(Math.min(1200, Math.max(450, dist * 0.55))),
  });
  await page.waitForTimeout(tourMs(250));
  await playClickEffect(page);
  await page.waitForTimeout(tourMs(200));
  await page.mouse.click(x, y);
  await page.waitForTimeout(tourMs(180));
}
