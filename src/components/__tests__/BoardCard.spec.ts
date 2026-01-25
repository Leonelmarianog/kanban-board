import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { DOMWrapper, mount } from '@vue/test-utils';
import BoardCard from '@/components/BoardCard.vue';

describe('BoardCard.vue', () => {
  beforeEach(() => {
    vi.spyOn(HTMLElement.prototype, 'getBoundingClientRect').mockReturnValue({
      x: 10,
      y: 20,
      width: 300,
      height: 120,
      top: 20,
      right: 310,
      bottom: 140,
      left: 10,
      toJSON: () => ({}),
    } as DOMRect);
  });

  afterEach(() => {
    vi.restoreAllMocks();
  });

  it('renders the card content', () => {
    const wrapper = mount(BoardCard, {
      props: {
        card: {
          id: 1,
          listId: 1,
          content: 'Hello card',
          labels: [],
        },
      },
    });

    expect(wrapper.text()).toContain('Hello card');
  });

  it('renders labels', () => {
    const wrapper = mount(BoardCard, {
      props: {
        card: {
          id: 1,
          listId: 1,
          content: 'With labels',
          labels: [
            { name: 'Bug', color: '#f00' },
            { name: 'Chore', color: '#0f0' },
          ],
        },
      },
      global: {
        stubs: {
          CardLabel: {
            name: 'CardLabel',
            props: ['label'],
            template: `<span id="card-label-stub">{{ label.name }}:{{ label.color }}</span>`,
          },
        },
      },
    });
    const labels = wrapper.findAll('#card-label-stub');

    expect(labels).toHaveLength(2);
    expect((labels[0] as DOMWrapper<HTMLElement>).text()).toBe('Bug:#f00');
    expect((labels[1] as DOMWrapper<HTMLElement>).text()).toBe('Chore:#0f0');
  });

  it('renders the edit button', () => {
    const wrapper = mount(BoardCard, {
      props: {
        card: {
          id: 1,
          listId: 1,
          content: 'Icon test',
          labels: [],
        },
      },
    });
    const button = wrapper.find('button');

    expect(button.exists()).toBe(true);
  });

  it('emits "edit" event with card data and bounding rect when edit button is clicked', async () => {
    const wrapper = mount(BoardCard, {
      props: {
        card: {
          id: 42,
          listId: 1,
          content: 'Editable content',
          labels: [],
        },
      },
    });

    await wrapper.find('button').trigger('click');

    const editEvent = wrapper.emitted('edit');
    const [card, rect] = editEvent![0] as [Record<string, unknown>, DOMRect];

    expect(card).toEqual({ id: 42, listId: 1, content: 'Editable content', labels: [] });
    expect(rect).toMatchObject({
      top: 20,
      left: 10,
      width: 300,
      height: 120,
    });
  });
});
