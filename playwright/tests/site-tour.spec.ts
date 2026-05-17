import { test, expect } from '@playwright/test';
import { smoothScrollToBottom } from '../lib/smooth-scroll.js';
import { tourMs } from '../lib/tour-speed.js';
import {
  humanLinkClick,
  humanNavClick,
  installVirtualCursor,
  moveCursorToMainContent,
} from '../lib/virtual-cursor.js';

/** グローバルナビの表示順（helpers.php の kaju_blog_nav_items と同じ） */
const NAV_LABELS = [
  '栽培記録',
  '庭の木',
  '作業メモ',
  'プロフィール',
  'お問い合わせ',
] as const;

/** 上→下スクロール（トップ） */
const SCROLL_DOWN_TOP_MS = 4_667;
/** 上→下スクロール（各ページ） */
const SCROLL_DOWN_PAGE_MS = 4_000;

async function waitForPageReady(page: import('@playwright/test').Page): Promise<void> {
  await page.locator('main').waitFor({ state: 'visible' });
  await page.evaluate(async () => {
    if (document.fonts?.ready) {
      await document.fonts.ready;
    }
  });
}

test('サイトツアー: トップ → 各ナビページを上から下までスクロール（擬似カーソル付き）', async ({
  page,
}) => {
  test.setTimeout(600_000);

  await installVirtualCursor(page);

  // --- トップ ---
  await page.goto('/');
  await waitForPageReady(page);
  await expect(page.locator('.top-kv')).toBeVisible();

  await page.waitForTimeout(tourMs(2_000));
  await smoothScrollToBottom(page, {
    durationMs: tourMs(SCROLL_DOWN_TOP_MS),
    pauseBeforeMs: 0,
  });

  const nav = page.getByRole('navigation', { name: 'メインナビゲーション' });

  for (const label of NAV_LABELS) {
    // 現ページでナビをクリック → 遷移
    await humanNavClick(page, nav.getByRole('link', { name: label, exact: true }));
    await waitForPageReady(page);

    if (label === '栽培記録') {
      const thirdCard = page.locator('.record-list__card-list .c-log-card').nth(2);
      await expect(thirdCard).toBeVisible();
      await page.waitForTimeout(tourMs(600));
      await humanLinkClick(page, thirdCard);
      await waitForPageReady(page);
      await expect(page.locator('.single-header__title')).toBeVisible();
    }

    await moveCursorToMainContent(page);
    await smoothScrollToBottom(page, {
      durationMs: tourMs(SCROLL_DOWN_PAGE_MS),
      pauseBeforeMs: 0,
    });
  }
});
