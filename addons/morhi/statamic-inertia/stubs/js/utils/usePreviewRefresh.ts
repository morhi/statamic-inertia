import { router } from '@inertiajs/vue3'
import { onMounted, onUnmounted } from 'vue'

export const usePreviewRefresh = () => {
    function onPreviewMessage(e: MessageEvent) {
        if (e.data?.name !== 'statamic.preview.updated') return
        router.reload({ headers: { 'X-Statamic-Token': e.data.token } })
    }

    onMounted(() => window.addEventListener('message', onPreviewMessage))
    onUnmounted(() => window.removeEventListener('message', onPreviewMessage))
}