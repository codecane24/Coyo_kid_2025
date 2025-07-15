import React, { createContext, useContext, useState, ReactNode } from "react";

// 1. Define the shape of your form data
interface FormData {
  personalInfo: {
    name?: string;
    email?: string;
    // Add all other personal fields
  };
  financialInfo: {
    income?: string;
    documents?: string[];
    // Add all other financial fields
  };
}

// 2. Define the context type
interface AdmissionFormContextType {
  formData: FormData;
  setFormData: React.Dispatch<React.SetStateAction<FormData>>;
}

// 3. Create context
const AdmissionFormContext = createContext<AdmissionFormContextType | undefined>(undefined);

// 4. Context provider
export const AdmissionFormProvider = ({ children }: { children: ReactNode }) => {
  const [formData, setFormData] = useState<FormData>({
    personalInfo: {},
    financialInfo: {},
  });

  return (
    <AdmissionFormContext.Provider value={{ formData, setFormData }}>
      {children}
    </AdmissionFormContext.Provider>
  );
};

// 5. Hook to use context
export const useAdmissionForm = () => {
  const context = useContext(AdmissionFormContext);
  if (!context) {
    throw new Error("useAdmissionForm must be used within an AdmissionFormProvider");
  }
  return context;
};
