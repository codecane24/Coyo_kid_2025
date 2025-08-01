import React from "react";
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

import ReactDOM from "react-dom/client";
import { BrowserRouter } from "react-router-dom";
import { base_path } from "./environment";
import "../node_modules/bootstrap/dist/css/bootstrap.min.css";
import "../src/style/css/feather.css";
import "../src/index.scss";
import store from "./core/data/redux/store";
import { Provider } from "react-redux";
import "../src/style/icon/boxicons/boxicons/css/boxicons.min.css";
import "../src/style/icon/weather/weathericons.css";
import "../src/style/icon/typicons/typicons.css";
import "../src/style/icon/fontawesome/css/fontawesome.min.css";
import "../src/style/icon/fontawesome/css/all.min.css";
import "../src/style/icon/ionic/ionicons.css";
import "../src/style/icon/tabler-icons/webfont/tabler-icons.css";
import ALLRoutes from "./feature-module/router/router";
import "../node_modules/bootstrap/dist/js/bootstrap.bundle.min.js";
import { AuthProvider } from "./context/AuthContext";
import { AdmissionFormProvider } from "./context/AdmissionFormContext";
import { AppProviders } from "./context/AppProviders";
import { ToastContainer } from "react-toastify";
import "react-toastify/dist/ReactToastify.css";

const root = ReactDOM.createRoot(
  document.getElementById("root") as HTMLElement
);
root.render(
  <React.StrictMode>
    <Provider store={store}>
      <BrowserRouter basename={base_path}>
        <AppProviders>
          <ALLRoutes />
          <ToastContainer position="top-right" autoClose={3000} />
        </AppProviders>
      </BrowserRouter>
    </Provider>
  </React.StrictMode>
);
