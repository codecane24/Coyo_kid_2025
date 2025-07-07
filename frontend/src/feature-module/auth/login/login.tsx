import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import { useAuth } from "../../../context/AuthContext";
import axios from "../../../utils/axiosInstance";

import ImageWithBasePath from "../../../core/common/imageWithBasePath";

// Axios Interceptor to add token to headers
axios.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem("authToken");
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => Promise.reject(error)
);

const Login = () => {
  const navigate = useNavigate();
  const { setUser, setToken } = useAuth(); // ✅ grab setUser and setToken from context
  const [formData, setFormData] = useState({
    username: "",
    password: "",
  });

  const [errors, setErrors] = useState({
    username: "",
    password: "",
  });

  const [branches, setBranches] = useState<any[]>([]);
  const [selectedBranch, setSelectedBranch] = useState("");
  const [userRole, setUserRole] = useState("");
  const [showBranchDropdown, setShowBranchDropdown] = useState(false);

  const validateForm = () => {
    const newErrors = { username: "", password: "" };
    let isValid = true;

    if (!formData.username.trim()) {
      newErrors.username = "Username is required";
      isValid = false;
    }

    if (!formData.password.trim()) {
      newErrors.password = "Password is required";
      isValid = false;
    }

    setErrors(newErrors);
    return isValid;
  };

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
    setErrors((prev) => ({ ...prev, [name]: "" }));
  };

const handleSubmit = async (e: React.FormEvent) => {
  e.preventDefault();
  if (!validateForm()) return;

  try {
    const response = await axios.post(
      "https://coyokid.abbangles.com/backend/api/v1/login",
      formData
    );

    const result = response.data;

    // Check for success status
    if (result.status !== "success") {
      alert(result.message || "Login failed.");
      return;
    }

    const { token, user, redirect, branches  } = result.data;

    
      // ✅ Save in context + localStorage
      setToken(token);
      setUser(user);
      localStorage.setItem("authToken", token);
      localStorage.setItem("user", JSON.stringify(user));
      setUserRole(user.type);

      // ✅ Branch handling
      if (branches?.length === 1) {
        localStorage.setItem("selectedBranch", JSON.stringify(branches[0]));
        redirectToDashboard(user.type);
      } else if (branches?.length > 1) {
        setBranches(branches);
        setShowBranchDropdown(true);
      } else {
        alert("No branches assigned to this user.");
      }
    } catch (error: any) {
      console.error("Login error:", error);
      alert("Login failed. Please check credentials.");
    }
  };
const handleBranchSelect = () => {
    if (!selectedBranch) return alert("Please select a branch.");
    const selected = branches.find((b) => b.id === selectedBranch);
    if (selected) {
      localStorage.setItem("selectedBranch", JSON.stringify(selected));
      redirectToDashboard(userRole);
    }
  };

  const redirectToDashboard = (role: string) => {
    switch (role) {
      case "admin":
        navigate("/index");
        break;
      case "teacher":
        navigate("/teacher-dashboard");
        break;
      case "student":
      case "parent":
        navigate("/student-dashboard");
        break;
      default:
        navigate("/unauthorized");
    }
  };



  return (
    <div className="container">
      <div className="row justify-content-center">
        <div className="col-md-5 mx-auto">
          <form onSubmit={handleSubmit}>
            <div className="d-flex flex-column justify-content-between vh-100">
              <div className="mx-auto p-4 text-center">
                <div style={{ maxWidth: "120px", margin: "0 auto" }}>
                  <ImageWithBasePath
                    src="assets/img/authentication/kidzy.png"
                    className="img-fluid"
                    alt="Logo"
                  />
                </div>
              </div>

              <div className="card">
                <div className="card-body p-4">
                  <div className="mb-4 text-center">
                    <h2 className="mb-2">Welcome</h2>
                    <p className="mb-0">Please enter your details to sign in</p>
                  </div>

                  {!showBranchDropdown && (
                    <>
                      <div className="mb-3">
                        <label className="form-label">Mobile / Email / School-ID</label>
                        <input
                          type="text"
                          className={`form-control ${errors.username && "is-invalid"}`}
                          name="username"
                          value={formData.username}
                          onChange={handleInputChange}
                        />
                        {errors.username && (
                          <div className="invalid-feedback">{errors.username}</div>
                        )}
                      </div>

                      <div className="mb-3">
                        <label className="form-label">Password</label>
                        <input
                          type="password"
                          className={`form-control ${errors.password && "is-invalid"}`}
                          name="password"
                          value={formData.password}
                          onChange={handleInputChange}
                        />
                        {errors.password && (
                          <div className="invalid-feedback">{errors.password}</div>
                        )}
                      </div>

                      <div className="mb-3">
                        <button type="submit" className="btn btn-primary w-100">
                          Sign In
                        </button>
                      </div>
                    </>
                  )}

                  {showBranchDropdown && (
                    <div className="mb-3">
                      <label className="form-label">Select Branch</label>
                      <select
                        className="form-control"
                        value={selectedBranch}
                        onChange={(e) => setSelectedBranch(e.target.value)}
                      >
                        <option value="">-- Choose Branch --</option>
                        {branches.map((branch: any, idx) => (
                          <option key={idx} value={branch.id}>
                            {branch.name}
                          </option>
                        ))}
                      </select>
                      <button
                        type="button"
                        className="btn btn-success mt-3 w-100"
                        onClick={handleBranchSelect}
                        disabled={!selectedBranch}
                      >
                        Continue
                      </button>
                    </div>
                  )}
                </div>
              </div>

              <div className="p-4 text-center">
                <div className="d-inline-flex align-items-center gap-2">
                  <span>Powered by</span>
                  <img
                    src="assets/img/authentication/kidzy.png"
                    alt="Logo"
                    style={{ height: "20px" }}
                  />
                </div>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  );
};

export default Login;
