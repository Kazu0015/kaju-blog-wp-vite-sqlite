import type { Page } from '@playwright/test';
import { tourMs } from './tour-speed.js';

export type SmoothScrollOptions = {
  /** スクロールにかける時間（ms） */
  durationMs?: number;
  /** スクロール開始前の待機（ms） */
  pauseBeforeMs?: number;
};

/**
 * ページ最下部まで ease-in-out でなめらかにスクロールする。
 */
export async function smoothScrollToBottom(
  page: Page,
  options: SmoothScrollOptions = {},
): Promise<void> {
  const { durationMs = tourMs(12_000), pauseBeforeMs = tourMs(500) } = options;

  if (pauseBeforeMs > 0) {
    await page.waitForTimeout(pauseBeforeMs);
  }

  await page.evaluate(
    ({ durationMs: duration }) =>
      new Promise<void>((resolve) => {
        const maxScroll = Math.max(
          0,
          document.documentElement.scrollHeight - window.innerHeight,
        );
        if (maxScroll <= 0) {
          resolve();
          return;
        }

        const startY = window.scrollY;
        const startTime = performance.now();

        const easeInOutCubic = (t: number) =>
          t < 0.5 ? 4 * t * t * t : 1 - Math.pow(-2 * t + 2, 3) / 2;

        const step = (now: number) => {
          const elapsed = now - startTime;
          const t = Math.min(elapsed / duration, 1);
          const eased = easeInOutCubic(t);
          window.scrollTo(0, startY + (maxScroll - startY) * eased);
          if (t < 1) {
            requestAnimationFrame(step);
          } else {
            resolve();
          }
        };

        requestAnimationFrame(step);
      }),
    { durationMs },
  );

  await page.waitForTimeout(tourMs(400));
}

export async function scrollToTop(page: Page): Promise<void> {
  await page.evaluate(() => window.scrollTo({ top: 0, behavior: 'instant' }));
}
