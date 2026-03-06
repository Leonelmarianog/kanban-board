import { describe, it, expect } from 'vitest';
import { useToggle } from '../useToggle';

describe('useToggle', () => {
  it('should initialize with default value (false)', () => {
    const { isVisible } = useToggle();
    expect(isVisible.value).toBe(false);
  });

  it('should initialize with provided value', () => {
    const { isVisible } = useToggle(true);
    expect(isVisible.value).toBe(true);
  });

  it('should set isVisible to true when show is called', () => {
    const { isVisible, show } = useToggle(false);
    show();
    expect(isVisible.value).toBe(true);
  });

  it('should set isVisible to false when hide is called', () => {
    const { isVisible, hide } = useToggle(true);
    hide();
    expect(isVisible.value).toBe(false);
  });

  it('should toggle isVisible when toggle is called', () => {
    const { isVisible, toggle } = useToggle(false);

    toggle();
    expect(isVisible.value).toBe(true);

    toggle();
    expect(isVisible.value).toBe(false);
  });
});
