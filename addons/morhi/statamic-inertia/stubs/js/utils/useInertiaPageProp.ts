import { usePage } from "@inertiajs/vue3"

export const useInertiaPageProp = <T>(prop: string): T | null => {
    const { props } = usePage();

    return props[prop] as T ?? null;
}