import { test, expect } from "@playwright/test";

test("Create project", async ({ page }) => {
  await page.goto("/");

  await page
    .getByRole("button", { name: "Log in with Employee login", exact: true })
    .click();

  await page.getByLabel("Username").fill("user");
  await page.getByLabel("Password").fill("user");
  await page.getByRole("button", { name: "Log in", exact: true }).click();

  await page.getByRole("link", { name: "Projekter" }).click();

  await page.getByRole("link", { name: "Create new project" }).click();

  await page.getByLabel("Title", { exact: true }).fill("My first project");
  await page
    .getByLabel("Description", { exact: true })
    .fill("This is my first project.");
  await page.getByRole("button", { name: "Save", exact: true }).click();
});
