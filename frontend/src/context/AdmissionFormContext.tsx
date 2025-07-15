import React, { createContext, useContext, useState, ReactNode } from "react";

// 1. Define the shape of your form data
interface FormData {
  personalInfo: {
    name?: string;
    email?: string;
    // add all personal fields
  };
  financialInfo: {
    income?: string;
    documents?: string[];
    // add all financial fields
  };
}

// 2. Define the context type
interface AdmissionFormContextType {
  formData: FormData;
  setFormData: (data: Partial<FormData>) => void;
}

// 3. Create context
const AdmissionFormContext = createContext<AdmissionFormContextType | undefined>(undefined);

// 4. Context provider
export const AdmissionFormProvider = ({ children }: { children: ReactNode }) => {
  const [formData, setFormDataState] = useState<FormData>({
    personalInfo: {},
    financialInfo: {},
  });

  // This merges new data into existing state
  const setFormData = (data: Partial<FormData>) => {
    setFormDataState((prev) => ({
      ...prev,
      ...data,
    }));
  };

  return (
    <AdmissionFormContext.Provider value={{ formData, setFormData }}>
      {children}
    </AdmissionFormContext.Provider>
  );
};

// 5. Hook to use context
export const useAdmissionForm = () => {
  const context = useContext(AdmissionFormContext);
  if (!context) throw new Error("useAdmissionForm must be used within AdmissionFormProvider");
  return context;
};
