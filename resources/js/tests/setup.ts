import { config } from '@vue/test-utils';

config.global.stubs = {
    'Link': {
        template: '<a :href="$attrs.href"><slot /></a>',
    },
};
