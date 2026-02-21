export abstract class BaseFormData<TFormDataInterface extends object> {
  protected appendToFormData<K extends keyof TFormDataInterface>(
    formData: FormData,
    key: K,
    value: TFormDataInterface[K],
  ): void {
    formData.append(key as string, String(value));
  }

  abstract toFormData(): FormData;
}
