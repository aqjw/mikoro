import { Mark } from '@tiptap/core';

declare module '@tiptap/core' {
    interface Commands<ReturnType> {
        spoiler: {
            /**
             * Set an spoiler mark
             * @example editor.commands.setSpoiler()
             */
            setSpoiler: () => ReturnType;
            /**
             * Toggle an spoiler mark
             * @example editor.commands.toggleSpoiler()
             */
            toggleSpoiler: () => ReturnType;
            /**
             * Unset an spoiler mark
             * @example editor.commands.unsetSpoiler()
             */
            unsetSpoiler: () => ReturnType;
        };
    }
}

/**
 * This extension allows you to create spoiler text.
 */
export const Spoiler = Mark.create<SpoilerOptions>({
    name: 'spoiler',

    priority: 1000,

    parseHTML() {
        return [{ tag: 'spoiler' }];
    },

    renderHTML() {
        return ['spoiler', {}, 0];
    },

    addCommands() {
        return {
            setSpoiler: () => ({ commands }) => {
                return commands.setMark(this.name);
            },
            toggleSpoiler: () => ({ commands }) => {
                return commands.toggleMark(this.name);
            },
            unsetSpoiler: () => ({ commands }) => {
                return commands.unsetMark(this.name);
            },
        };
    },
});
