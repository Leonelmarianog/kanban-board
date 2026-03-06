import { BaseFormData } from '@/forms/BaseFormData.ts';
import type { RegisterRequest } from '@/api/backend/types.ts';

export class RegisterFormData extends BaseFormData<RegisterRequest> {
  public constructor(
    public readonly first_name: string,
    public readonly last_name: string,
    public readonly email: string,
    public readonly password: string,
    public readonly password_confirmation: string,
  ) {
    super();
  }

  public toFormData(): FormData {
    const formData = new FormData();

    this.appendToFormData(formData, 'first_name', this.first_name);
    this.appendToFormData(formData, 'last_name', this.last_name);
    this.appendToFormData(formData, 'email', this.email);
    this.appendToFormData(formData, 'password', this.password);
    this.appendToFormData(formData, 'password_confirmation', this.password_confirmation);

    return formData;
  }
}
