import React from "react";
import { Link, useNavigate } from "react-router-dom";
import { all_routes } from "../../router/all_routes";
import ImageWithBasePath from "../../../core/common/imageWithBasePath";

const ForgotPassword = () => {
  const routes = all_routes;
  const navigation = useNavigate();

  const navigationPath = () => {
    navigation(routes.resetPassword);
  };
  return (
    <>
      <div className="container-fluid">
  <div className="login-wrapper w-100 overflow-hidden position-relative d-block vh-100">
    <div className="row justify-content-center align-items-center vh-100">
      <div className="col-md-6 col-sm-10 mx-auto p-4">
        <form>
      <div className="mx-auto mb-5 text-center">
  <div style={{ maxWidth: '250px', margin: '0 auto' }}>
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
                <h2 className="mb-2">Forgot Password?</h2>
                <p className="mb-0">
                  If you forgot your password, we’ll email you password.
                </p>
              </div>
              <div className="mb-3">
                <label className="form-label">Mobile / Email / School-ID</label>
                <div className="input-icon mb-3 position-relative">
                  <span className="input-icon-addon">
                    <i className="ti ti-mail" />
                  </span>
                  <input type="email" className="form-control" />
                </div>
              </div>
              <div className="mb-3">
                <button
                  onClick={navigationPath}
                  type="submit"
                  className="btn btn-primary w-100"
                >
                  Sign In
                </button>
              </div>
              <div className="text-center">
                <h6 className="fw-normal text-dark mb-0">
                  Return to{" "}
                  <Link to={routes.login} className="hover-a">
                    Login
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
        </form>
      </div>
    </div>
  </div>
</div>

    </>
  );
};

export default ForgotPassword;
