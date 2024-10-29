import Session from '@/Plugins/Session';
import { useUserStore } from '@/Stores';

interface PlaybackStateParams {
    episode_id: number;
    translation_id: number;
    time: number;
}

interface Events {
    success?: () => void;
    error?: (error: any) => void;
}

export default {
    async $fetchPlaybackState(titleId: number) {
        const userStore = useUserStore();
        if (userStore.isLogged) {
            const response = await axios.get(route('upi.title.playback_state', titleId));
            this.playbackState[titleId] = response.data;
        } else {
            const sessionData = Session.get(`playback-${titleId}`);
            if (sessionData) {
                this.playbackState[titleId] = sessionData;
            }
        }
    },

    async $fetchLinks(titleId: number, events: Events) {
        try {
            const response = await axios.get(
                route('upi.title.video_links', {
                    title: titleId,
                    episode: this.getPlaybackStateEpisodeId(titleId),
                })
            );

            this.videoLinks[titleId] = response.data.links;
            events.success?.();
        } catch (error) {
            console.error('Error fetching links:', error);
            events.error?.(error);
        }
    },

    async $fetchEpisodes(titleId: number, events: Events) {
        try {
            const response = await axios.get(route('upi.title.episodes', { title: titleId }));

            this.episodes[titleId] = response.data;
            events.success?.();
        } catch (error) {
            console.error('Error fetching episodes:', error);
            events.error?.(error);
        }
    },

    async $setPlaybackState(titleId: number, params: PlaybackStateParams) {
        this.playbackState[titleId] = params;
    },

    async $savePlaybackState(titleId: number) {
        const params = this.playbackState[titleId];
        const userStore = useUserStore();

        if (userStore.isLogged) {
            await axios.post(route('upi.title.playback_state', titleId), params);
        } else {
            Session.set(`playback-${titleId}`, params);
        }
    },
};
