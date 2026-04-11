export type StoyanVideo = {
    id: number;
    title: string;
    slug: string;
    description: string | null;
    videoUrl: string | null;
    thumbnailUrl: string | null;
};

export type StoyanCategory = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    previewImageUrl: string | null;
    videoCount: number;
    opensDirectly: boolean;
    videos: StoyanVideo[];
    directVideo: StoyanVideo | null;
};

export type StoyanHotspot = {
    key: string;
    label: string;
    x: number;
    y: number;
    width: number;
    height: number;
};

export type StoyanCategoriesByHotspot = Record<string, StoyanCategory | undefined>;