import { describe, it, expect, vi, beforeEach, afterEach } from 'vitest';
import { useClickOutside } from '../useClickOutside';
import { defineComponent } from 'vue';
import { mount } from '@vue/test-utils';

describe('useClickOutside', () => {
  let callback: () => void;

  beforeEach(() => {
    callback = vi.fn();
  });

  afterEach(() => {
    vi.clearAllMocks();
  });

  it('should call callback when clicking outside the element', async () => {
    const TestComponent = defineComponent({
      setup() {
        const ref = useClickOutside(callback);
        return { ref };
      },
      template: '<div><div ref="ref" data-testid="inside">Inside</div></div>',
    });

    mount(TestComponent);

    // Dispatch native click on the document (outside the ref'd element)
    document.dispatchEvent(new MouseEvent('click'));

    expect(callback).toHaveBeenCalledTimes(1);
  });

  it('should not call callback when clicking inside the element', async () => {
    const TestComponent = defineComponent({
      setup() {
        const ref = useClickOutside(callback);
        return { ref };
      },
      template: '<div ref="ref" data-testid="inside">Inside</div>',
    });

    const wrapper = mount(TestComponent);

    // Get the actual DOM element
    const element = wrapper.find('[data-testid="inside"]').element;

    // Dispatch native click on the element itself
    element.dispatchEvent(new MouseEvent('click', { bubbles: true }));

    expect(callback).not.toHaveBeenCalled();
  });

  it('should remove event listener on unmount', async () => {
    const addSpy = vi.spyOn(document, 'addEventListener');
    const removeSpy = vi.spyOn(document, 'removeEventListener');

    const TestComponent = defineComponent({
      setup() {
        const ref = useClickOutside(callback);
        return { ref };
      },
      template: '<div ref="ref">Test</div>',
    });

    const wrapper = mount(TestComponent);

    expect(addSpy).toHaveBeenCalledWith('click', expect.any(Function));

    wrapper.unmount();

    expect(removeSpy).toHaveBeenCalledWith('click', expect.any(Function));

    // Restore spies to avoid affecting other tests that rely on document.addEventListener
    addSpy.mockRestore();
    removeSpy.mockRestore();
  });
});
