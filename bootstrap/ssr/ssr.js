import { onMounted, onUnmounted, defineComponent, mergeProps, unref, withCtx, createTextVNode, toDisplayString, useSSRContext, ref, createVNode, resolveDynamicComponent, createSSRApp, h } from "vue";
import { ssrRenderAttrs, ssrRenderComponent, ssrRenderList, ssrInterpolate, ssrRenderSlot, ssrRenderClass, ssrRenderAttr, ssrRenderVNode, renderToString } from "vue/server-renderer";
import { usePage, router, Link, Head, createInertiaApp } from "@inertiajs/vue3";
import createServer from "@inertiajs/vue3/server";
const useInertiaPageProp = (prop) => {
  const { props } = usePage();
  return props[prop] ?? null;
};
const usePreviewRefresh = () => {
  function onPreviewMessage(e) {
    if (e.data?.name !== "statamic.preview.updated") return;
    router.reload({ headers: { "X-Statamic-Token": e.data.token } });
  }
  onMounted(() => window.addEventListener("message", onPreviewMessage));
  onUnmounted(() => window.removeEventListener("message", onPreviewMessage));
};
const _sfc_main$8 = /* @__PURE__ */ defineComponent({
  __name: "Layout",
  __ssrInlineRender: true,
  setup(__props) {
    const nav = useInertiaPageProp("nav");
    usePreviewRefresh();
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "layout min-h-screen bg-[#080810] text-white antialiased" }, _attrs))}><header class="fixed top-0 left-0 right-0 z-50 flex items-center justify-between px-8 py-5 backdrop-blur-sm bg-[#080810]/70 border-b border-white/10">`);
      _push(ssrRenderComponent(unref(Link), {
        href: "/",
        class: "text-sm font-semibold tracking-widest uppercase text-white/80 hover:text-white transition-colors"
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(` Studio `);
          } else {
            return [
              createTextVNode(" Studio ")
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`<nav class="flex items-center gap-8"><!--[-->`);
      ssrRenderList(unref(nav), (item) => {
        _push(ssrRenderComponent(unref(Link), {
          key: item.href,
          href: item.href,
          class: "text-sm text-white/60 hover:text-white transition-colors"
        }, {
          default: withCtx((_, _push2, _parent2, _scopeId) => {
            if (_push2) {
              _push2(`${ssrInterpolate(item.label)}`);
            } else {
              return [
                createTextVNode(toDisplayString(item.label), 1)
              ];
            }
          }),
          _: 2
        }, _parent));
      });
      _push(`<!--]--></nav></header><div class="pt-[73px]">`);
      ssrRenderSlot(_ctx.$slots, "default", {}, null, _push, _parent);
      _push(`</div></div>`);
    };
  }
});
const _sfc_setup$8 = _sfc_main$8.setup;
_sfc_main$8.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Layout.vue");
  return _sfc_setup$8 ? _sfc_setup$8(props, ctx) : void 0;
};
const __vite_glob_0_0$1 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: _sfc_main$8
}, Symbol.toStringTag, { value: "Module" }));
const _sfc_main$7 = /* @__PURE__ */ defineComponent({
  ...{ inheritAttrs: false },
  __name: "Accordion",
  __ssrInlineRender: true,
  props: {
    items: {}
  },
  setup(__props) {
    const open = ref(null);
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "block-accordion py-20 px-8" }, _attrs))}><div class="max-w-3xl mx-auto"><div class="rounded-2xl bg-white/[0.03] backdrop-blur-xl border border-white/[0.07] overflow-hidden divide-y divide-white/[0.06]"><!--[-->`);
      ssrRenderList(__props.items, (item, index) => {
        _push(`<div><button class="w-full text-left flex items-center justify-between px-8 py-6 text-white hover:bg-white/[0.04] transition-colors duration-200"><span class="font-medium text-base pr-8">${ssrInterpolate(item.question)}</span><span class="${ssrRenderClass([open.value === index ? "border-sky-400 text-sky-400 rotate-45" : "border-white/20 text-white/40", "shrink-0 w-6 h-6 rounded-full border flex items-center justify-center text-xs transition-all duration-300"])}">+</span></button>`);
        if (open.value === index) {
          _push(`<div class="border-l-2 border-sky-400 mx-8 mb-6"><div class="pl-6 py-1 text-gray-400 text-sm leading-relaxed prose prose-invert prose-sm max-w-none">${item.answer ?? ""}</div></div>`);
        } else {
          _push(`<!---->`);
        }
        _push(`</div>`);
      });
      _push(`<!--]--></div></div></div>`);
    };
  }
});
const _sfc_setup$7 = _sfc_main$7.setup;
_sfc_main$7.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Blocks/Accordion.vue");
  return _sfc_setup$7 ? _sfc_setup$7(props, ctx) : void 0;
};
const __vite_glob_0_0 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: _sfc_main$7
}, Symbol.toStringTag, { value: "Module" }));
const _sfc_main$6 = /* @__PURE__ */ defineComponent({
  ...{ inheritAttrs: false },
  __name: "CardGrid",
  __ssrInlineRender: true,
  props: {
    cards: {}
  },
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "block-card-grid py-20 px-8" }, _attrs))}><div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 max-w-6xl mx-auto"><!--[-->`);
      ssrRenderList(__props.cards, (card) => {
        _push(`<a${ssrRenderAttr("href", card.link || "#")} class="group relative rounded-2xl overflow-hidden bg-white/[0.04] backdrop-blur-sm border border-white/[0.07] hover:border-sky-400/30 hover:bg-white/[0.07] transition-all duration-300 flex flex-col"><div class="relative overflow-hidden h-52">`);
        if (card.image?.url) {
          _push(`<img${ssrRenderAttr("src", card.image.url)}${ssrRenderAttr("srcset", card.image.srcset)} sizes="(max-width: 640px) 100vw, (max-width: 1024px) 50vw, 33vw"${ssrRenderAttr("alt", card.title)} class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">`);
        } else {
          _push(`<!---->`);
        }
        _push(`<div class="absolute inset-0 bg-gradient-to-t from-[#080810]/80 to-transparent"></div></div><div class="p-6 flex flex-col flex-1">`);
        if (card.title) {
          _push(`<h3 class="text-base font-semibold text-white mb-2">${ssrInterpolate(card.title)}</h3>`);
        } else {
          _push(`<!---->`);
        }
        if (card.text) {
          _push(`<p class="text-gray-400 text-sm leading-relaxed flex-1">${ssrInterpolate(card.text)}</p>`);
        } else {
          _push(`<!---->`);
        }
        _push(`<div class="mt-5 flex items-center gap-2 text-sky-400 text-sm font-medium"><span>Read more</span><span class="group-hover:translate-x-1 transition-transform duration-200">→</span></div></div></a>`);
      });
      _push(`<!--]--></div></div>`);
    };
  }
});
const _sfc_setup$6 = _sfc_main$6.setup;
_sfc_main$6.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Blocks/CardGrid.vue");
  return _sfc_setup$6 ? _sfc_setup$6(props, ctx) : void 0;
};
const __vite_glob_0_1$1 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: _sfc_main$6
}, Symbol.toStringTag, { value: "Module" }));
const _sfc_main$5 = /* @__PURE__ */ defineComponent({
  ...{ inheritAttrs: false },
  __name: "Hero",
  __ssrInlineRender: true,
  props: {
    title: {},
    subtitle: {},
    background_image: {},
    cta_label: {},
    cta_url: {}
  },
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<div${ssrRenderAttrs(mergeProps({
        class: "block-hero relative min-h-screen flex items-end bg-[#080810] overflow-hidden",
        style: __props.background_image?.url ? `background-image: url(${__props.background_image.url}); background-size: cover; background-position: center;` : ""
      }, _attrs))}><div class="absolute inset-0 bg-gradient-to-t from-[#080810] via-[#080810]/60 to-[#080810]/10"></div><div class="relative z-10 px-10 pb-20 pt-40 max-w-4xl"><div class="w-12 h-px bg-sky-400 mb-8"></div>`);
      if (__props.title) {
        _push(`<h1 class="text-6xl lg:text-8xl font-bold tracking-tight leading-none text-white mb-6">${ssrInterpolate(__props.title)}</h1>`);
      } else {
        _push(`<!---->`);
      }
      if (__props.subtitle) {
        _push(`<p class="text-lg text-gray-400 max-w-xl mb-10 leading-relaxed">${ssrInterpolate(__props.subtitle)}</p>`);
      } else {
        _push(`<!---->`);
      }
      if (__props.cta_label && __props.cta_url) {
        _push(`<a${ssrRenderAttr("href", __props.cta_url)} class="inline-flex items-center gap-3 border border-white/20 text-white px-7 py-3 rounded-full text-sm font-medium tracking-wide backdrop-blur-sm bg-white/5 hover:bg-white/10 hover:border-white/40 transition-all duration-300">${ssrInterpolate(__props.cta_label)} <span class="text-sky-400">→</span></a>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div></div>`);
    };
  }
});
const _sfc_setup$5 = _sfc_main$5.setup;
_sfc_main$5.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Blocks/Hero.vue");
  return _sfc_setup$5 ? _sfc_setup$5(props, ctx) : void 0;
};
const __vite_glob_0_2 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: _sfc_main$5
}, Symbol.toStringTag, { value: "Module" }));
const _sfc_main$4 = /* @__PURE__ */ defineComponent({
  ...{ inheritAttrs: false },
  __name: "ImageCaption",
  __ssrInlineRender: true,
  props: {
    image: {},
    caption: {},
    alignment: {}
  },
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "block-image-caption py-16 px-8" }, _attrs))}><figure class="relative max-w-5xl mx-auto"><div class="relative rounded-2xl overflow-hidden">`);
      if (__props.image?.url) {
        _push(`<img${ssrRenderAttr("src", __props.image.url)}${ssrRenderAttr("srcset", __props.image.srcset)} sizes="(max-width: 1280px) 100vw, 1280px"${ssrRenderAttr("alt", __props.caption)} class="w-full object-cover">`);
      } else {
        _push(`<!---->`);
      }
      if (__props.caption) {
        _push(`<figcaption class="absolute bottom-0 inset-x-0 px-6 py-4 bg-black/40 backdrop-blur-md border-t border-white/[0.08]"><p class="text-sm text-gray-300 tracking-wide">${ssrInterpolate(__props.caption)}</p></figcaption>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div></figure></div>`);
    };
  }
});
const _sfc_setup$4 = _sfc_main$4.setup;
_sfc_main$4.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Blocks/ImageCaption.vue");
  return _sfc_setup$4 ? _sfc_setup$4(props, ctx) : void 0;
};
const __vite_glob_0_3 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: _sfc_main$4
}, Symbol.toStringTag, { value: "Module" }));
const _sfc_main$3 = /* @__PURE__ */ defineComponent({
  ...{ inheritAttrs: false },
  __name: "Quote",
  __ssrInlineRender: true,
  props: {
    content: {},
    author: {},
    author_image: {}
  },
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "block-quote py-24 px-8" }, _attrs))}><div class="max-w-5xl mx-auto relative"><div class="relative rounded-3xl bg-white/[0.04] backdrop-blur-xl border border-white/[0.07] px-12 py-16 overflow-hidden"><span class="absolute -top-4 left-8 text-[12rem] leading-none font-serif text-white/[0.04] select-none pointer-events-none">“</span><blockquote class="relative z-10">`);
      if (__props.content) {
        _push(`<div class="text-2xl lg:text-3xl text-gray-100 leading-relaxed font-light italic mb-10 prose prose-invert max-w-none">${__props.content ?? ""}</div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`<footer class="flex items-center gap-5">`);
      if (__props.author_image?.url) {
        _push(`<img${ssrRenderAttr("src", __props.author_image.url)}${ssrRenderAttr("alt", __props.author)} class="w-11 h-11 rounded-full object-cover ring-2 ring-white/10">`);
      } else {
        _push(`<!---->`);
      }
      _push(`<div><div class="w-6 h-px bg-sky-400 mb-2"></div>`);
      if (__props.author) {
        _push(`<cite class="not-italic text-sm font-medium text-gray-300 tracking-wider uppercase">${ssrInterpolate(__props.author)}</cite>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div></footer></blockquote></div></div></div>`);
    };
  }
});
const _sfc_setup$3 = _sfc_main$3.setup;
_sfc_main$3.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Blocks/Quote.vue");
  return _sfc_setup$3 ? _sfc_setup$3(props, ctx) : void 0;
};
const __vite_glob_0_4 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: _sfc_main$3
}, Symbol.toStringTag, { value: "Module" }));
const _sfc_main$2 = /* @__PURE__ */ defineComponent({
  ...{ inheritAttrs: false },
  __name: "Text",
  __ssrInlineRender: true,
  props: {
    content: {}
  },
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "block-text px-8 py-12 max-w-3xl mx-auto" }, _attrs))}><div class="prose prose-invert prose-lg prose-p:text-gray-300 prose-headings:text-white prose-a:text-sky-400">${__props.content ?? ""}</div></div>`);
    };
  }
});
const _sfc_setup$2 = _sfc_main$2.setup;
_sfc_main$2.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Blocks/Text.vue");
  return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
const __vite_glob_0_5 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: _sfc_main$2
}, Symbol.toStringTag, { value: "Module" }));
const _sfc_main$1 = /* @__PURE__ */ defineComponent({
  __name: "Blocks",
  __ssrInlineRender: true,
  props: {
    blocks: Array
  },
  setup(__props) {
    const modules = /* @__PURE__ */ Object.assign({ "../Blocks/Accordion.vue": __vite_glob_0_0, "../Blocks/CardGrid.vue": __vite_glob_0_1$1, "../Blocks/Hero.vue": __vite_glob_0_2, "../Blocks/ImageCaption.vue": __vite_glob_0_3, "../Blocks/Quote.vue": __vite_glob_0_4, "../Blocks/Text.vue": __vite_glob_0_5 });
    const blockMap = Object.fromEntries(
      Object.entries(modules).map(([path, mod]) => {
        const name = path.replace("../Blocks/", "").replace(".vue", "");
        const handle = name.replace(/([A-Z])/g, "_$1").toLowerCase().replace(/^_/, "");
        return [handle, mod.default];
      })
    );
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "blocks" }, _attrs))}><!--[-->`);
      ssrRenderList(__props.blocks, (block) => {
        _push(`<!--[-->`);
        if (unref(blockMap)[block.type]) {
          ssrRenderVNode(_push, createVNode(resolveDynamicComponent(unref(blockMap)[block.type]), mergeProps({ ref_for: true }, block), null), _parent);
        } else {
          _push(`<!---->`);
        }
        _push(`<!--]-->`);
      });
      _push(`<!--]--></div>`);
    };
  }
});
const _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Components/Blocks.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
const _sfc_main = /* @__PURE__ */ defineComponent({
  ...{ layout: _sfc_main$8 },
  __name: "Page",
  __ssrInlineRender: true,
  props: {
    entry: {}
  },
  setup(__props) {
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<!--[-->`);
      _push(ssrRenderComponent(unref(Head), null, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<title${_scopeId}>${ssrInterpolate(__props.entry.data.title)}</title>`);
          } else {
            return [
              createVNode("title", null, toDisplayString(__props.entry.data.title), 1)
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`<main>`);
      _push(ssrRenderComponent(_sfc_main$1, {
        blocks: __props.entry.data.content_blocks
      }, null, _parent));
      _push(`</main><!--]-->`);
    };
  }
});
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/Pages/Pages/Page.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
const __vite_glob_0_1 = /* @__PURE__ */ Object.freeze(/* @__PURE__ */ Object.defineProperty({
  __proto__: null,
  default: _sfc_main
}, Symbol.toStringTag, { value: "Module" }));
createServer(
  (page) => createInertiaApp({
    page,
    render: renderToString,
    resolve: (name) => {
      const pages = /* @__PURE__ */ Object.assign({ "./Pages/Layout.vue": __vite_glob_0_0$1, "./Pages/Pages/Page.vue": __vite_glob_0_1 });
      return pages[`./Pages/${name}.vue`];
    },
    setup({ App, props, plugin }) {
      return createSSRApp({ render: () => h(App, props) }).use(plugin);
    }
  })
);
