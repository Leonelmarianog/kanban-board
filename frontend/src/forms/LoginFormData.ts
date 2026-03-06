import { BaseFormData } from '@/forms/BaseFormData.ts';
import type { LoginRequest } from '@/api/backend/types.ts';

export class LoginFormData extends BaseFormData<LoginRequest> {
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
