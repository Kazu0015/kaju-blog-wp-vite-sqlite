/** ツアー全体の速度倍率（大きいほど速い） */
export const TOUR_SPEED = 1.5;

/** 基準ミリ秒を速度倍率に合わせて短縮する */
export function tourMs(ms: number): number {
  return Math.max(1, Math.round(ms / TOUR_SPEED));
}
