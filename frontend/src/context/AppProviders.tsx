// src/context/AppProviders.tsx
import React from "react";
import { AuthProvider } from "./AuthContext";
import { AdmissionFormProvider } from "./AdmissionFormContext";

interface Props {
  children: React.ReactNode;
}

export const AppProviders: React.FC<Props> = ({ children }) => {
  return (
    <AuthProvider>
      <AdmissionFormProvider>
        {children}
      </AdmissionFormProvider>
    </AuthProvider>
  );
};
