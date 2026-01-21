import { test, expect } from "@playwright/test";

test("Create screening", async ({ page }) => {
  await page.goto("/");

  await page
    .getByRole("button", { name: "Log in with Employee login", exact: true })
    .click();

  await page.getByLabel("Username").fill("user");
  await page.getByLabel("Password").fill("user");
  await page.getByRole("button", { name: "Login", exact: true }).click();

  await page.getByRole("link", { name: "Screeninger" }).click();

  await page.getByRole("link", { name: "Create new screening" }).click();

  await page.getByLabel("Title", { exact: true }).fill("My first screening");
  await page
    .getByLabel("Description", { exact: true })
    .fill("This is my first screening.");
  await page.getByRole("button", { name: "Save", exact: true }).click();
});
