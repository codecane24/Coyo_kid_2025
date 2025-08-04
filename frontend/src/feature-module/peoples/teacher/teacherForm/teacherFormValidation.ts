// teacherFormValidation.ts
// Centralized validation logic for TeacherForm

export const requiredFields = [
  "id",
  "first_name",
  "last_name",
  "class",
  "subject",
  "gender",
  "phone",
  "email",
  "date_of_joining",
  "dob"
];

export function getMissingFields(formData: Record<string, any>): string[] {
  return requiredFields.filter(
    field => !formData[field] || formData[field].toString().trim() === ""
  );
}

export function isFormValid(formData: Record<string, any>): boolean {
  return getMissingFields(formData).length === 0;
}
