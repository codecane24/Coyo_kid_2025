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
  } else {
    console.log("User is still loading or not logged in.");
  }
}, [user]);
const isSuperAdmin = user?.user_id === 1; 

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
                <li>
                  <Link
                    to="#"
                    className="d-flex align-items-center border bg-white rounded p-2 mb-4"
                  >
                    <ImageWithBasePath
                      src="assets/img/icons/global-img.svg"
                      className="avatar avatar-md img-fluid rounded"
                      alt="Profile"
                    />
                    <span className="text-dark ms-2 fw-normal">
                      Global International
                    </span>
                  </Link>
                </li>
              </ul>

              <ul>
                
              { SidebarData?.map((mainLabel, index) => {
    // Filter the top-level submenuItems with access
const filteredSubmenuItems = Array.isArray(mainLabel?.submenuItems)
  ? (mainLabel.submenuItems as any[]).filter((title: any) => {
      if (isSuperAdmin) return true; // Allow everything for super admin

if (!isSuperAdmin && title?.permissionKey && !hasSidebarAccess(title.permissionKey)) return null;


      const hasDirectLink = !!title.link;
      const hasVisibleChildren =
        Array.isArray(title.submenuItems) &&
        title.submenuItems.some((link: any) =>
          hasSidebarAccess(link?.permissionKey || link?.label)
        );

      return hasDirectLink || hasVisibleChildren;
    })
  : [];


if (filteredSubmenuItems.length === 0) return null;


    return (
      <li key={index}>
        <h6 className="submenu-hdr">
          <span>{mainLabel?.label}</span>
        </h6>
        <ul>
          {filteredSubmenuItems.map((title: any) => {
    if (!isSuperAdmin && title?.permissionKey && !hasSidebarAccess(title.permissionKey)) return null;


            // Gather all allowed links
            let link_array: any = [];
          const filteredSubItems = title.submenuItems?.filter((link: any) =>
  isSuperAdmin || hasSidebarAccess(link?.permissionKey || link?.label)
);


            filteredSubItems?.forEach((link: any) => {
              link_array.push(link?.link);
              if (link?.submenu && Array.isArray(link.submenuItems)) {
                link.submenuItems
                  .filter((i: any) => isSuperAdmin || hasSidebarAccess(i.permissionKey || i.label))
                  .forEach((item: any) => link_array.push(item?.link));
              }
            });

            // Skip if no children and no direct link
            if (!title.link && (!filteredSubItems || filteredSubItems.length === 0)) return null;

            title.links = link_array;

            return (
              <li className="submenu" key={title.label}>
                <Link
                  to={title?.submenu ? "#" : title?.link}
                  onClick={() =>
                    handleClick(
                      title?.label,
                      title?.themeSetting,
                      getLayoutClass(title?.label)
                    )
                  }
                  className={`${
                    subOpen === title?.label ? "subdrop" : ""
                  } ${
                    title?.links?.includes(Location.pathname) ? "active" : ""
                  }`}
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
          })}
        </ul>
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
