import '../scss/main.scss';

/**
 * SP（md 未満）: KV を 1 枚ずつ切り替え（Ken Burns は SP では付与しない）。
 * リードは二重レイヤーだが SP では opacity トランジション無しで即時切替（SCSS）。
 * PC 幅ではグリッド表示のためタイマー停止・クラス解除・リードは初期文言に戻す。
 *
 * レイアウト調査: `?kvDebug=1` を付けて SP 幅で開くか、`sessionStorage.setItem('topKvDebug','1')` 後に再読込。
 * コンソールに .top-kv__lead / lead-body / 1行目 / アクティブ img の座標・高さ・transform が出る。
 * 手動計測: `window.__topKvDebugSnapshot('任意ラベル')`
 * 読み方: `leadBodyOffsetH` と `lead.top` が切替前後で不変なら DOM 上のリード移動はなく、
 *   見かけのズレは画像の transform / クロスフェード合成（視差）を疑う。`Δ vs syncDone` が opacity 中のドリフト。
 *
 * 犯人捜し: `?kvOff=leadHalo,imgFade` のようにカンマ区切り（main.js の KV_OFF_FLAGS 参照）。
 * html に `kv-off-<flag>` が付き、_top-kv.scss で該当スタイルだけ無効化。本番では付けない。
 */
const TOP_KV_SLIDE_MS = 7000;

/** @type {readonly string[]} 許可リスト（未知の名前は無視） */
const KV_OFF_FLAGS = [
  "leadHalo", // .top-kv__lead::before（外周ぼかし）
  "leadFill", // .top-kv__lead::after（内側白＋弱 blur）
  "leadBlur", // 上記両方
  "imgFade", // 画像 li の opacity transition
  "leadFade", // リード二層の opacity transition
  "kb", // Ken Burns（animation / transform）
  "leadIsolation", // .top-kv__lead の isolation
  "dualLead", // 非表示レイヤーを display:none（二層クロスフェード無効）
];

function applyKvOffFromQuery() {
  try {
    const raw = new URLSearchParams(window.location.search).get("kvOff");
    if (!raw) return;
    const root = document.documentElement;
    const wanted = raw.split(/[,+]/).map((s) => s.trim()).filter(Boolean);
    const applied = [];
    wanted.forEach((f) => {
      if (!KV_OFF_FLAGS.includes(f)) return;
      root.classList.add(`kv-off-${f}`);
      applied.push(f);
    });
    if (applied.length) {
      console.info("[kvOff] 一時無効化:", applied.join(", "));
    }
    const unknown = wanted.filter((f) => !KV_OFF_FLAGS.includes(f));
    if (unknown.length) {
      console.warn("[kvOff] 未対応（無視）:", unknown.join(", "), "| 使える値:", KV_OFF_FLAGS.join(", "));
    }
  } catch (_) {
    /* ignore */
  }
}

function isTopKvDebugEnabled() {
  try {
    const q = new URLSearchParams(window.location.search);
    if (q.has("kvDebug")) return true;
    if (window.sessionStorage?.getItem("topKvDebug") === "1") return true;
  } catch (_) {
    /* ignore */
  }
  return false;
}

function initTopKvSlideshow() {
  const list = document.querySelector(".top-kv__image-list");
  if (!list) return;

  const items = Array.from(list.querySelectorAll(".top-kv__image-item"));
  if (items.length === 0) return;

  const lead = document.querySelector(".top-kv__lead");
  const layers = lead ? Array.from(lead.querySelectorAll(".top-kv__lead-layer")) : [];
  const firstLayerParas =
    layers.length >= 1 ? Array.from(layers[0].querySelectorAll(".top-kv__lead-text")) : [];

  /** スライド順に [1行目, 2行目]。0番は index.html の初期文言をそのまま使う */
  const TOP_KV_LEAD_LINES =
    firstLayerParas.length >= 2
      ? [
          [firstLayerParas[0].textContent, firstLayerParas[1].textContent],
          ["さくらんぼが色づく春、", "待ちわびた、赤い贈りもの。"],
          ["ぶどうの棚に実る夏、", "手間をかけた分だけ、甘い。"],
          ["すももの収穫に感動、", "酸っぱくて甘い、庭の恵み。"],
          ["ブルーベリーが実る季節、", "小さな粒に、大きな喜び。"],
          ["プルーンが深く色づく秋、", "じっくり待った、甘い実り。"],
        ]
      : null;

  const mqMobile = window.matchMedia("(max-width: 767px)");
  const mqReduce = window.matchMedia("(prefers-reduced-motion: reduce)");
  const kvDebug = isTopKvDebugEnabled();

  const round2 = (n) => Math.round(n * 100) / 100;
  const rectPick = (el) => {
    if (!el) return null;
    const r = el.getBoundingClientRect();
    return {
      top: round2(r.top),
      left: round2(r.left),
      width: round2(r.width),
      height: round2(r.height),
      bottom: round2(r.bottom),
    };
  };

  const inner = document.querySelector(".top-kv__inner");

  const snapshotKvLayout = (phase) => {
    if (!kvDebug) return;
    const activeIdx = items.findIndex((li) => li.classList.contains("is-active"));
    const activeLi = activeIdx >= 0 ? items[activeIdx] : null;
    const activeImg = activeLi?.querySelector(".top-kv__image");
    const bodyEl = lead?.querySelector(".top-kv__lead-body");
    const visibleLayer = layers.find((ly) => ly.classList.contains("top-kv__lead-layer--visible"));
    const firstLine = visibleLayer?.querySelector(".top-kv__lead-text");

    const payload = {
      phase,
      perfMs: round2(performance.now()),
      slideIndex: index,
      activeItemIdx: activeIdx,
      inner: rectPick(inner),
      imageList: rectPick(list),
      lead: rectPick(lead),
      leadBody: rectPick(bodyEl),
      leadBodyOffsetH: bodyEl?.offsetHeight ?? null,
      leadBodyClientH: bodyEl?.clientHeight ?? null,
      layerVisible: layers.map((ly, i) => ({
        i,
        visible: ly.classList.contains("top-kv__lead-layer--visible"),
        offsetH: ly.offsetHeight,
        rect: rectPick(ly),
      })),
      firstLeadLine: rectPick(firstLine),
      activeImg: rectPick(activeImg),
      activeImgTransform: activeImg ? getComputedStyle(activeImg).transform : null,
      activeImgKb: activeImg?.classList.contains("top-kv__image--kb") ?? null,
    };
    console.log(`[kvDebug] ${phase}`, payload);
    return payload;
  };

  if (kvDebug) {
    console.info(
      "[kvDebug] 計測ON。?kvDebug=1 または sessionStorage.topKvDebug='1'。SP幅でスライド切替を見る。",
    );
    window.__topKvDebugSnapshot = (label = "manual") => snapshotKvLayout(label);
  }

  let index = 0;
  let timerId = null;
  /** `.top-kv__lead-layer` のうち前面になっているインデックス（0 または 1） */
  let leadVisibleLayerIndex = 0;

  const stop = () => {
    if (timerId !== null) {
      clearInterval(timerId);
      timerId = null;
    }
  };

  /** 両レイヤー同じ文言・前面固定（初回表示・PC 復帰時） */
  const syncLeadInstant = (i) => {
    if (!TOP_KV_LEAD_LINES || layers.length < 2) return;
    const lines = TOP_KV_LEAD_LINES[i];
    if (!lines) return;
    layers.forEach((layer) => {
      const ps = layer.querySelectorAll(".top-kv__lead-text");
      if (ps.length >= 2) {
        ps[0].textContent = lines[0];
        ps[1].textContent = lines[1];
      }
      layer.classList.remove("top-kv__lead-layer--visible");
    });
    layers[0].classList.add("top-kv__lead-layer--visible");
    leadVisibleLayerIndex = 0;
    layers[0].setAttribute("aria-hidden", "false");
    layers[1].setAttribute("aria-hidden", "true");
  };

  /** 裏レイヤーに次文言を入れてから opacity を入れ替え */
  const setLeadCrossfade = (i) => {
    if (!TOP_KV_LEAD_LINES || layers.length < 2) return;
    const lines = TOP_KV_LEAD_LINES[i];
    if (!lines) return;
    const fromIdx = leadVisibleLayerIndex;
    const toIdx = 1 - fromIdx;
    const fromLayer = layers[fromIdx];
    const toLayer = layers[toIdx];
    const ps = toLayer.querySelectorAll(".top-kv__lead-text");
    if (ps.length < 2) return;
    if (kvDebug) snapshotKvLayout(`lead:beforeTextSwap slide=${i}`);
    ps[0].textContent = lines[0];
    ps[1].textContent = lines[1];
    if (kvDebug) snapshotKvLayout(`lead:afterTextSwap slide=${i}`);
    if (kvDebug) snapshotKvLayout(`lead:beforeClassSwap slide=${i}`);
    fromLayer.classList.remove("top-kv__lead-layer--visible");
    toLayer.classList.add("top-kv__lead-layer--visible");
    fromLayer.setAttribute("aria-hidden", "true");
    toLayer.setAttribute("aria-hidden", "false");
    leadVisibleLayerIndex = toIdx;
    if (kvDebug) snapshotKvLayout(`lead:afterClassSwap slide=${i}`);
  };

  const restartKenBurns = (img) => {
    if (!img || mqReduce.matches) return;
    /* SP: rect は不変でもクリップ内の写ちが動き、固定リードに対して「ずれ」に見えることがある */
    if (mqMobile.matches) return;
    img.classList.remove("top-kv__image--kb");
    void img.offsetWidth;
    img.classList.add("top-kv__image--kb");
  };

  const apply = (nextIndex, { instantLead } = {}) => {
    const prevDomIndex = index;
    const normalized = ((nextIndex % items.length) + items.length) % items.length;
    if (kvDebug) {
      snapshotKvLayout(
        `apply:enter prevDomIndex=${prevDomIndex}→target=${normalized} instantLead=${Boolean(instantLead)}`,
      );
    }

    index = normalized;

    const hadActive = items.some((li) => li.classList.contains("is-active"));
    const incomingLi = items[index];
    const incomingImg = incomingLi?.querySelector(".top-kv__image");

    /* SP: アクティブ化の後に restart すると、前回表示済みスライドは forwards 終端→identity に
     一瞬スナップしつつ opacity が上がり、手前のリードが縦に滑ったように見える。
     非表示のうち（is-active 付与前）に入り画像だけ振り直す。初回は hadActive が false のため後段で実行。 */
    if (mqMobile.matches && hadActive && incomingImg && !incomingLi.classList.contains("is-active")) {
      restartKenBurns(incomingImg);
    }

    items.forEach((li, idx) => {
      li.classList.toggle("is-active", idx === index);
    });

    if (mqMobile.matches && !hadActive && incomingImg) {
      restartKenBurns(incomingImg);
    }
    if (mqMobile.matches) {
      if (instantLead) {
        syncLeadInstant(index);
      } else {
        setLeadCrossfade(index);
      }
    }

    if (kvDebug) {
      const slideTag = index;
      const syncSnap = snapshotKvLayout(`apply:syncDone slide=${slideTag}`);
      requestAnimationFrame(() => {
        const a = snapshotKvLayout(`apply:rAF1 slide=${slideTag}`);
        requestAnimationFrame(() => {
          const b = snapshotKvLayout(`apply:rAF2 slide=${slideTag}`);
          if (a && b && a.lead && b.lead) {
            console.log("[kvDebug] Δ lead (rAF2 - rAF1)", {
              dTop: round2(b.lead.top - a.lead.top),
              dHeight: round2(b.lead.height - a.lead.height),
              dFirstLineTop: round2(
                (b.firstLeadLine?.top ?? 0) - (a.firstLeadLine?.top ?? 0),
              ),
            });
          }
        });
      });

      /* リード／画像の opacity 中に座標が動くか（SP はリード即時・KB なし想定） */
      if (mqMobile.matches && !instantLead && syncSnap) {
        [400, 900, 1450].forEach((ms) => {
          window.setTimeout(() => {
            const mid = snapshotKvLayout(`apply:t+${ms}ms slide=${slideTag}`);
            if (!mid?.lead || !syncSnap.lead) return;
            console.log(`[kvDebug] Δ vs syncDone (t+${ms}ms)`, {
              dLeadTop: round2(mid.lead.top - syncSnap.lead.top),
              dFirstLineTop: round2(
                (mid.firstLeadLine?.top ?? 0) - (syncSnap.firstLeadLine?.top ?? 0),
              ),
              dImgTop: round2((mid.activeImg?.top ?? 0) - (syncSnap.activeImg?.top ?? 0)),
              imgTransform: mid.activeImgTransform,
            });
          }, ms);
        });
      }
    }
  };

  const start = () => {
    stop();
    if (!mqMobile.matches) return;
    index = 0;
    apply(0, { instantLead: true });
    const delay = mqReduce.matches ? TOP_KV_SLIDE_MS * 2 : TOP_KV_SLIDE_MS;
    timerId = window.setInterval(() => {
      apply(index + 1);
    }, delay);
  };

  const sync = () => {
    stop();
    if (mqMobile.matches) {
      start();
    } else {
      items.forEach((li) => {
        li.classList.remove("is-active");
        const img = li.querySelector(".top-kv__image");
        if (img) {
          img.classList.remove("top-kv__image--kb");
        }
      });
      index = 0;
      syncLeadInstant(0);
    }
  };

  if (typeof mqMobile.addEventListener === "function") {
    mqMobile.addEventListener("change", sync);
  } else {
    mqMobile.addListener(sync);
  }

  sync();
}

function bootTopKv() {
  applyKvOffFromQuery();
  initTopKvSlideshow();
}

function initLikeButtons() {
  document.querySelectorAll(".c-log-card__meta-like").forEach((icon) => {
    icon.addEventListener("click", (e) => {
      e.stopPropagation();
      icon.classList.toggle("is-liked");
    });
  });
}

/** PC幅ではアコーディオンをすべて展開、SP幅では閉じた状態から操作可能にする */
function initPrivacyPolicySections() {
  const root = document.querySelector(".js-privacy-policy-sections");
  if (!root) return;

  const items = root.querySelectorAll(".privacy-policy-section");
  const mqDesktop = window.matchMedia("(min-width: 768px)");

  const sync = () => {
    items.forEach((item) => {
      if (mqDesktop.matches) {
        item.setAttribute("open", "");
      } else {
        item.removeAttribute("open");
      }
    });
  };

  sync();

  if (typeof mqDesktop.addEventListener === "function") {
    mqDesktop.addEventListener("change", sync);
  } else {
    mqDesktop.addListener(sync);
  }
}

applyKvOffFromQuery();
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    bootTopKv();
    initLikeButtons();
    initPrivacyPolicySections();
  });
} else {
  bootTopKv();
  initLikeButtons();
  initPrivacyPolicySections();
}
