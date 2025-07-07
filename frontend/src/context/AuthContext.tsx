import React, { createContext, useContext, useState } from "react";

interface User {
  name?: string;
  type?: string;
  role?: string;
  [key: string]: any;
}
interface AuthContextProps {
  user: User | null;
  token: string | null;
  logout: () => void;
  setUser: (user: User | null) => void;
  setToken: (token: string | null) => void;
}


const AuthContext = createContext<AuthContextProps | undefined>(undefined);

export const AuthProvider: React.FC<{ children: React.ReactNode }> = ({ children }) => {
  // 👉 Immediately read from localStorage on init
  const [user, setUser] = useState<User | null>(() => {
    const storedUser = localStorage.getItem("user");
    return storedUser ? JSON.parse(storedUser) : null;
  });

  const [token, setToken] = useState<string | null>(() => {
    return localStorage.getItem("authToken");
  });
const logout = () => {
  localStorage.clear();
  setUser(null);
  setToken(null);
};


  return (
  <AuthContext.Provider value={{ user, token, logout, setUser, setToken }}>

      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = (): AuthContextProps => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error("useAuth must be used within an AuthProvider");
  }
  return context;
};
