import React, { useState } from "react";
import { Link, useNavigate } from "react-router-dom";
import { all_routes } from "../../router/all_routes";
import ImageWithBasePath from "../../../core/common/imageWithBasePath";

type PasswordField = "password";

const Login = () => {
  const routes = all_routes;
  const navigate = useNavigate();

  const [passwordVisibility, setPasswordVisibility] = useState({
    password: false,
  });

  const [formData, setFormData] = useState({
    username: "",
    password: "",
  });

  const [errors, setErrors] = useState({
    username: "",
    password: "",
  });

  const togglePasswordVisibility = (field: PasswordField) => {
    setPasswordVisibility((prevState) => ({
      ...prevState,
      [field]: !prevState[field],
    }));
  };

  const handleInputChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const { name, value } = e.target;
    setFormData((prev) => ({ ...prev, [name]: value }));
    setErrors((prev) => ({ ...prev, [name]: "" }));
  };

  const validateForm = () => {
    let isValid = true;
    const newErrors = { username: "", password: "" };

    if (!formData.username.trim()) {
      newErrors.username = "Username is required";
      isValid = false;
    } else if (formData.username.trim().length < 6) {
      newErrors.username = "Username must be at least 6 characters";
      isValid = false;
    }

    if (!formData.password.trim()) {
      newErrors.password = "Password is required";
      isValid = false;
    } else if (formData.password.trim().length < 6) {
      newErrors.password = "Password must be at least 6 characters";
      isValid = false;
    }

    setErrors(newErrors);
    return isValid;
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (validateForm()) {
      // Replace this with actual login logic later
      navigate(routes.adminDashboard);
    }
  };

  return (
    <div className="container">
      <div className="row justify-content-center">
        <div className="col-md-5 mx-auto">
          <form onSubmit={handleSubmit}>
            <div className="d-flex flex-column justify-content-between vh-100">
              <div className="mx-auto p-4 text-center">
                <ImageWithBasePath
                  src="assets/img/authentication/kidzy.png"
                  className="img-fluid"
                  alt="Logo"
                />
              </div>
              <div className="card">
                <div className="card-body p-4">
                  <div className="mb-4">
                    <h2 className="mb-2">Welcome</h2>
                    <p className="mb-0">Please enter your details to sign in</p>
                  </div>

                  <div className="mb-3">
                    <label className="form-label">Mobile / Email / School-ID</label>
                    <div className="input-icon mb-2 position-relative">
                      <span className="input-icon-addon">
                        <i className="ti ti-user" />
                      </span>
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

                    <label className="form-label">Password</label>
                    <div className="pass-group position-relative">
                      <input
                        type={passwordVisibility.password ? "text" : "password"}
                        className={`form-control ${errors.password && "is-invalid"}`}
                        name="password"
                        value={formData.password}
                        onChange={handleInputChange}
                      />
                      <span
                        className={`ti toggle-passwords ${
                          passwordVisibility.password ? "ti-eye" : "ti-eye-off"
                        }`}
                        onClick={() => togglePasswordVisibility("password")}
                      ></span>
                      {errors.password && (
                        <div className="invalid-feedback d-block">{errors.password}</div>
                      )}
                    </div>
                  </div>

                  <div className="form-wrap form-wrap-checkbox mb-3">
                    <div className="d-flex align-items-center">
                      <div className="form-check form-check-md mb-0">
                        <input className="form-check-input mt-0" type="checkbox" />
                      </div>
                      <p className="ms-1 mb-0">Remember Me</p>
                    </div>
                    <div className="text-end">
                      <Link to={routes.forgotPassword} className="link-danger">
                        Forgot Password?
                      </Link>
                    </div>
                  </div>

                  <div className="mb-3">
                    <button type="submit" className="btn btn-primary w-100">
                      Sign In
                    </button>
                  </div>

                  <div className="text-center">
                    <h6 className="fw-normal text-dark mb-0">
                      Don’t have an account?{" "}
                      <Link to={routes.register3} className="hover-a">
                        Create Account
                      </Link>
                    </h6>
                  </div>
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
 