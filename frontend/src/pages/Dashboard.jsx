import React, { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import NavBar from "../components/NavBar";
import { getTextColourFromBrightness } from "../utils/utils";
import { useAppContext } from "../context/AppContext";

export default function Dashboard() {
    const navigate = useNavigate();
    const [children, setChildren] = useState([]);
    const [accountsMap, setAccountsMap] = useState({});
    const { activeHousehold, apiFetch, logout } = useAppContext();

    useEffect(() => {
        if (activeHousehold) {
            apiFetch(`households/${activeHousehold.id}/children`, logout, {
                method: "GET",
            })
                .then((response) => {
                    return response.json();
                })
                .then((data) => {
                    if (data.member && Array.isArray(data.member)) {
                        const childrenData = data.member;
                        setChildren(childrenData);

                        childrenData.forEach((child) => {
                            apiFetch(`children/${child.id}/accounts`, logout, {
                                method: "GET",
                            })
                                .then((response) => {
                                    return response.json();
                                })
                                .then((accountData) => {
                                    if (
                                        accountData.member &&
                                        Array.isArray(accountData.member)
                                    ) {
                                        const accounts =
                                            accountData.member.flatMap(
                                                (item) => item.accounts || [],
                                            );
                                        setAccountsMap((prevAccountsMap) => ({
                                            ...prevAccountsMap,
                                            [child.id]: accounts,
                                        }));
                                    } else {
                                        setAccountsMap((prevAccountsMap) => ({
                                            ...prevAccountsMap,
                                            [child.id]: [],
                                        }));
                                    }
                                })
                                .catch((error) =>
                                    console.error(
                                        "Error fetching accounts data:",
                                        error,
                                    ),
                                );
                        });
                    } else {
                        setChildren([]);
                    }
                })
                .catch((error) =>
                    console.error("Error fetching children data:", error),
                );
        }
    }, [navigate, activeHousehold]);
    const handleChildClick = (childId) => {
        navigate(`/household/${activeHousehold.id}/child/${childId}`);
    };
    const handleAccountClick = (childId, accountId) => {
        navigate(
            `/household/${activeHousehold.id}/child/${childId}/account/${accountId}`,
        );
    };

    const handleCreateTransaction = (childId, accountId) => {
        navigate(
            `/household/${activeHousehold.id}/child/${childId}/account/${accountId}/transaction/add`,
        );
    };

    return (
        <div>
            <NavBar />
            <div className="container mt-5">
                <div className="row">
                    {children.map((child) => {
                        const childAccounts = accountsMap[child.id] || [];
                        const totalBalance = childAccounts.reduce(
                            (sum, account) => sum + (account.balance ?? 0),
                            0,
                        );

                        return (
                            <div
                                key={child.id}
                                className="col-md-6 col-lg-4 mb-4"
                                onClick={() => handleChildClick(child.id)}
                                style={{ cursor: "pointer" }}
                            >
                                <div
                                    className="card shadow-sm border-0"
                                    style={{ backgroundColor: "#f8f9fa" }}
                                >
                                    <div className="card-body">
                                        <h5 className="card-title text-primary">
                                            {child.name}
                                        </h5>
                                        <div className="mt-3">
                                            <ul className="list-unstyled">
                                                {childAccounts.length > 0
                                                    ? childAccounts.map(
                                                          (account) => {
                                                              const backgroundColor =
                                                                  account.color ||
                                                                  "#ffffff";
                                                              const textColor =
                                                                  getTextColourFromBrightness(
                                                                      backgroundColor,
                                                                  );

                                                              return (
                                                                  <li
                                                                      key={
                                                                          account[
                                                                              "@id"
                                                                          ]
                                                                      }
                                                                      className="mb-2 d-flex justify-content-between align-items-center"
                                                                      onClick={(
                                                                          e,
                                                                      ) => {
                                                                          e.stopPropagation();
                                                                          handleAccountClick(
                                                                              child.id,
                                                                              account.id,
                                                                          );
                                                                      }}
                                                                      style={{
                                                                          border: "1px solid #ddd",
                                                                          padding:
                                                                              "10px",
                                                                          borderRadius:
                                                                              "5px",
                                                                          backgroundColor:
                                                                              backgroundColor,
                                                                          color: textColor,
                                                                      }}
                                                                  >
                                                                      <div className="d-flex align-items-center">
                                                                          {account.icon && (
                                                                              <i
                                                                                  className={`bi ${account.icon} me-2`}
                                                                              ></i>
                                                                          )}
                                                                          <span>
                                                                              {
                                                                                  account.name
                                                                              }
                                                                              :
                                                                          </span>{" "}
                                                                          <span>
                                                                              $
                                                                              {account.balance?.toFixed(
                                                                                  2,
                                                                              ) ??
                                                                                  "N/A"}
                                                                          </span>
                                                                      </div>
                                                                      <button
                                                                          onClick={(
                                                                              e,
                                                                          ) => {
                                                                              e.stopPropagation();
                                                                              handleCreateTransaction(
                                                                                  child.id,
                                                                                  account[
                                                                                      "@id"
                                                                                  ]
                                                                                      .split(
                                                                                          "/",
                                                                                      )
                                                                                      .pop(),
                                                                              );
                                                                          }}
                                                                          className="btn btn-success btn-sm"
                                                                      >
                                                                          +
                                                                      </button>
                                                                  </li>
                                                              );
                                                          },
                                                      )
                                                    : null}
                                            </ul>
                                        </div>
                                        <p className="card-text text-secondary">
                                            Total Balance:{" "}
                                            <strong className="text-dark">
                                                ${totalBalance.toFixed(2)}
                                            </strong>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        );
                    })}
                </div>
            </div>
        </div>
    );
}
