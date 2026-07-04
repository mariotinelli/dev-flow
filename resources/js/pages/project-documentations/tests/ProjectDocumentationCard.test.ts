import { mount } from '@vue/test-utils';
import { describe, expect, test, vi } from 'vitest';
import type { ProjectDocumentation } from '@/types';
import ProjectDocumentationCard from '../partials/ProjectDocumentationCard.vue';

function createLinkDocumentation(overrides: Partial<ProjectDocumentation> = {}): ProjectDocumentation {
    return {
        id: 1,
        title: 'API Guide',
        description: 'How to use the API',
        type: 3,
        type_label: 'Link',
        category: 3,
        category_label: 'API',
        visibility: 1,
        visibility_label: 'Membros do projeto',
        url: 'https://example.com/api-guide',
        download_url: null,
        author: { id: 1, name: 'John' },
        created_at: '2026-01-01',
        can: { update: true, delete: true },
        ...overrides,
    };
}

const formOptions = {
    categories: [{ value: 3, label: 'API' }],
    types: [{ value: 3, label: 'Link' }],
    visibilities: [{ value: 1, label: 'Membros do projeto' }],
};

describe('ProjectDocumentationCard', () => {
    test('renders link documentation title as external URL with target="_blank"', () => {
        const doc = createLinkDocumentation();

        const wrapper = mount(ProjectDocumentationCard, {
            props: { projectDocumentation: doc, ...formOptions },
        });

        const anchor = wrapper.find('a[href="https://example.com/api-guide"]');

        expect(anchor.exists()).toBe(true);
        expect(anchor.attributes('target')).toBe('_blank');
        expect(anchor.attributes('rel')).toBe('noreferrer');
        expect(anchor.text()).toContain('API Guide');
    });

    test('link documentation card renders edit button when can.update is true', () => {
        const doc = createLinkDocumentation({ can: { update: true, delete: false } });

        const wrapper = mount(ProjectDocumentationCard, {
            props: { projectDocumentation: doc, ...formOptions },
        });

        expect(wrapper.find('[data-testid="edit-button"]').exists()).toBe(true);
    });

    test('link documentation card does not render edit button when can.update is false', () => {
        const doc = createLinkDocumentation({ can: { update: false, delete: false } });

        const wrapper = mount(ProjectDocumentationCard, {
            props: { projectDocumentation: doc, ...formOptions },
        });

        expect(wrapper.find('[data-testid="edit-button"]').exists()).toBe(false);
    });

    test('link documentation card renders delete button when can.delete is true', () => {
        const doc = createLinkDocumentation({ can: { update: false, delete: true } });

        const wrapper = mount(ProjectDocumentationCard, {
            props: { projectDocumentation: doc, ...formOptions },
        });

        expect(wrapper.find('[data-testid="delete-button"]').exists()).toBe(true);
    });

    test('link documentation card does not render delete button when can.delete is false', () => {
        const doc = createLinkDocumentation({ can: { update: true, delete: false } });

        const wrapper = mount(ProjectDocumentationCard, {
            props: { projectDocumentation: doc, ...formOptions },
        });

        expect(wrapper.find('[data-testid="delete-button"]').exists()).toBe(false);
    });

    test('link documentation card does not display "Sem ações" when user has no update/delete permission', () => {
        const doc = createLinkDocumentation({ can: { update: false, delete: false } });

        const wrapper = mount(ProjectDocumentationCard, {
            props: { projectDocumentation: doc, ...formOptions },
        });

        expect(wrapper.text()).not.toContain('Sem ações');
    });

    test('link documentation card exposes copy-link button that copies URL to clipboard', async () => {
        const writeText = vi.fn();
        Object.assign(navigator, { clipboard: { writeText } });

        const doc = createLinkDocumentation();

        const wrapper = mount(ProjectDocumentationCard, {
            props: { projectDocumentation: doc, ...formOptions },
        });

        const copyButton = wrapper.find('[data-testid="copy-link-button"]');
        expect(copyButton.exists()).toBe(true);

        await copyButton.trigger('click');

        expect(writeText).toHaveBeenCalledWith('https://example.com/api-guide');
    });
});
