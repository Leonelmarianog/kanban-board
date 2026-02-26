import { BaseFormData } from '@/forms/BaseFormData.ts';
import type { LoginRequestInterface } from '@/services/backend/auth/types.ts';

export class LoginFormData extends BaseFormData<LoginRequestInterface> {
  public constructor(
    public readonly email: string,
    public readonly password: string,
  ) {
    super();
  }

  public toFormData(): FormData {
    const formData = new FormData();

    this.appendToFormData(formData, 'email', this.email);
    this.appendToFormData(formData, 'password', this.password);

    return formData;
  }
}
