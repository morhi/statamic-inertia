/// <reference types="vite/client" />

interface AssetField {
  url: string
  srcset: string
  alt: string
}

interface Block extends Record<string, any> {
  type: string;
}

type Blocks = Block[]