import React, { useEffect, useState} from "react";
import { useNavigate } from "react-router-dom";
import { Link, useLocation } from "react-router-dom";
import Scrollbars from "react-custom-scrollbars-2";
import { SidebarData } from "../../data/json/sidebarData";
import ImageWithBasePath from "../imageWithBasePath";
import "../../../style/icon/tabler-icons/webfont/tabler-icons.css";
import { setExpandMenu } from "../../data/redux/sidebarSlice";
import { useDispatch } from "react-redux";
import { AuthProvider } from "../../../context/AuthContext";
import { useAuth } from "../../../context/AuthContext";
import {
  resetAllMode,
  setDataLayout,
} from "../../data/redux/themeSettingSlice";
import usePreviousRoute from "./usePreviousRoute";
import { usePermission } from "../../../hooks/usePermission";


const Sidebar = () => {
  const { user } = useAuth();
useEffect(() => {
  if (user) {
    console.log("User ID:", user.id);
    if (user?.company_id && user?.branch_id) {
      localStorage.setItem("companyId", user.company_id.toString());
      localStorage.setItem("branchId", user.branch_id.toString());
      console.log("✅ Stored companyId & branchId in localStorage");
    }
  } else {
    console.log("User is still loading or not logged in.");
  }
}, [user]);
const isSuperAdmin = user?.user_id === 1; 
const branchName = useAuth().user?.branch_name;
    const { hasSidebarAccess } = usePermission()
    console.log("User ID:", user?.user_id);

    console.log(user)
  const Location = useLocation();
   const { logout } = useAuth();
const navigate = useNavigate();
const handleLogout = () => {
  logout();
  navigate("/");
};


  const [subOpen, setSubopen] = useState<any>("");
  const [subsidebar, setSubsidebar] = useState("");

  const toggleSidebar = (title: any) => {
    localStorage.setItem("menuOpened", title);
    if (title === subOpen) {
      setSubopen("");
    } else {
      setSubopen(title);
    }
  };

  const toggleSubsidebar = (subitem: any) => {
    if (subitem === subsidebar) {
      setSubsidebar("");
    } else {
      setSubsidebar(subitem);
    }
  };

  const handleLayoutChange = (layout: string) => {
    dispatch(setDataLayout(layout));
  };

  const handleClick = (label: any, themeSetting: any, layout: any) => {
    toggleSidebar(label);
    if (themeSetting) {
      handleLayoutChange(layout);
    }
  };

  const getLayoutClass = (label: any) => {
    switch (label) {
      case "Default":
        return "default_layout";
      case "Mini":
        return "mini_layout";
      case "Box":
        return "boxed_layout";
      case "Dark":
        return "dark_data_theme";
      case "RTL":
        return "rtl";
      default:
        return "";
    }
  };
  const location = useLocation();
  const dispatch = useDispatch();
  const previousLocation = usePreviousRoute();

  useEffect(() => {
    const layoutPages = [
      "/layout-dark",
      "/layout-rtl",
      "/layout-mini",
      "/layout-box",
      "/layout-default",
    ];

    const isCurrentLayoutPage = layoutPages.some((path) =>
      location.pathname.includes(path)
    );
    const isPreviousLayoutPage =
      previousLocation &&
      layoutPages.some((path) => previousLocation.pathname.includes(path));

    if (isPreviousLayoutPage && !isCurrentLayoutPage) {
      dispatch(resetAllMode());
    }
  }, [location, previousLocation, dispatch]);

  useEffect(() => {
    setSubopen(localStorage.getItem("menuOpened"));
    // Select all 'submenu' elements
    const submenus = document.querySelectorAll(".submenu");
    // Loop through each 'submenu'
    submenus.forEach((submenu) => {
      // Find all 'li' elements within the 'submenu'
      const listItems = submenu.querySelectorAll("li");
      submenu.classList.remove("active");
      // Check if any 'li' has the 'active' class
      listItems.forEach((item) => {
        if (item.classList.contains("active")) {
          // Add 'active' class to the 'submenu'
          submenu.classList.add("active");
          return;
        }
      });
    });
  }, [Location.pathname]);

  const onMouseEnter = () => {
    dispatch(setExpandMenu(true));
  };
  const onMouseLeave = () => {
    dispatch(setExpandMenu(false));
  };
  return (
    <>
      <div
        className="sidebar"
        id="sidebar"
        onMouseEnter={onMouseEnter}
        onMouseLeave={onMouseLeave}
      >
        <Scrollbars>
          <div className="sidebar-inner slimscroll">
            <div id="sidebar-menu" className="sidebar-menu">
<ul>
  <li className="mb-4">
    <Link
      to="#"
      className="d-flex align-items-center border bg-white rounded p-2 shadow-sm"
      style={{ textDecoration: "none" }}
    >
      <ImageWithBasePath
        src="assets/img/icons/global-img.svg"
        className="avatar avatar-md img-fluid rounded"
        alt="Profile"
      />
      <div className="ms-3 d-flex flex-column">
        <div className="fw-semibold text-dark" style={{ fontSize: "1rem" }}>
          Global International
        </div>
        <div
          className="mt-1 px-2 py-1"
          style={{
            fontSize: "0.75rem",
            color: "#666",
            backgroundColor: "#F2F2F2",
            borderRadius: "6px",
            width: "fit-content",
            marginTop: "4px",
          }}
        >
          Branch: {branchName}
        </div>
      </div>
    </Link>
  </li>
</ul>



              <ul>
{SidebarData?.map((mainLabel, index) => {
  const filteredSubmenuItems = Array.isArray(mainLabel?.submenuItems)
    ? (mainLabel.submenuItems as any[]).map((title: any) => {
        if (isSuperAdmin) return title;

        const hasPermission = title?.permissionKey
          ? hasSidebarAccess(title.permissionKey)
          : true;

        const filteredSubItems = Array.isArray(title.submenuItems)
          ? title.submenuItems.filter((link: any) =>
              hasSidebarAccess(link?.permissionKey || link?.label)
            )
          : [];

        const hasDirectLink = !!title.link;

        if (!hasPermission) return null;
        if (!hasDirectLink && filteredSubItems.length === 0) return null;

        return {
          ...title,
          submenuItems: filteredSubItems,
        };
      }).filter(Boolean)
    : [];

  // ✅ ACTUAL RENDERED LI COMPONENTS – pre-render
  const renderedChildren = filteredSubmenuItems
    .map((title: any) => {
      const hasPermission =
        isSuperAdmin || hasSidebarAccess(title.permissionKey || title.label);

      if (!hasPermission) return null;

      const filteredSubItems = title.submenuItems?.filter((link: any) =>
        isSuperAdmin || hasSidebarAccess(link?.permissionKey || link?.label)
      );

      const hasVisible = !!title.link || (filteredSubItems && filteredSubItems.length > 0);
      if (!hasVisible) return null;

      // Optional: compute link_array if needed
      const link_array: any[] = [];
      filteredSubItems?.forEach((link: any) => {
        link_array.push(link?.link);
        if (link?.submenu && Array.isArray(link.submenuItems)) {
          link.submenuItems
            .filter((i: any) => isSuperAdmin || hasSidebarAccess(i.permissionKey || i.label))
            .forEach((item: any) => link_array.push(item?.link));
        }
      });
      title.links = link_array;

      // ✅ Return <li> here — this is the rendered child
      return (
        <li className="submenu" key={title.label}>
          <Link
            to={title?.submenu ? "#" : title?.link}
            onClick={() =>
              handleClick(title?.label, title?.themeSetting, getLayoutClass(title?.label))
            }
            className={`${
              subOpen === title?.label ? "subdrop" : ""
            } ${title?.links?.includes(Location.pathname) ? "active" : ""}`}
          >
            <i className={title.icon}></i>
            <span>{title?.label}</span>
            {title?.version && (
              <span className="badge badge-primary badge-xs text-white fs-10 ms-auto">
                {title.version}
              </span>
            )}
            {title?.submenu && <span className="menu-arrow" />}
          </Link>

          {title?.submenu !== false &&
            subOpen === title?.label &&
            filteredSubItems &&
            filteredSubItems.length > 0 && (
              <ul style={{ display: "block" }}>
                {filteredSubItems.map((item: any) => {
                  const filteredNested = item?.submenuItems?.filter((i: any) =>
                    isSuperAdmin || hasSidebarAccess(i.permissionKey || i.label)
                  );

                  if (!item.link && (!filteredNested || filteredNested.length === 0))
                    return null;

                  return (
                    <li
                      className={item?.submenuItems ? "submenu submenu-two" : ""}
                      key={item.label}
                    >
                      <Link
                        to={item?.link}
                        className={`${
                          item?.link === Location.pathname ? "active" : ""
                        } ${subsidebar === item?.label ? "subdrop" : ""}`}
                        onClick={() => toggleSubsidebar(item?.label)}
                      >
                        {item?.label}
                        {item?.submenu && <span className="menu-arrow" />}
                      </Link>

                      {item?.submenuItems &&
                        subsidebar === item?.label &&
                        filteredNested &&
                        filteredNested.length > 0 && (
                          <ul style={{ display: "block" }}>
                            {filteredNested.map((i: any) => (
                              <li key={i.label}>
                                <Link
                                  to={i?.link}
                                  className={`${
                                    i?.link === Location.pathname ? "active" : ""
                                  }`}
                                >
                                  {i.label}
                                </Link>
                              </li>
                            ))}
                          </ul>
                        )}
                    </li>
                  );
                })}
              </ul>
            )}
        </li>
      );
    })
    .filter(Boolean); // 🔥 This ensures only valid <li>s remain

  // ✅ Only show the parent if it has children
  if (renderedChildren.length === 0) return null;

  // ✅ Safe to render
  return (
    <li key={index}>
      <h6 className="submenu-hdr">
        <span>{mainLabel?.label}</span>
      </h6>
      <ul>{renderedChildren}</ul>
    </li>
  );
})}

<ul className="mt-4 px-3">



   <button
    onClick={handleLogout}
    className="btn logout-btn w-100 d-flex align-items-center justify-content-start px-4 py-2 gap-2"
  >
    <i className="fas fa-sign-out-alt"></i>
    <span className="fw-semibold">Logout</span>
  </button>

</ul>

              </ul>
            </div>
          </div>
        </Scrollbars>
      </div>
    </>
  );
};

export default Sidebar;
