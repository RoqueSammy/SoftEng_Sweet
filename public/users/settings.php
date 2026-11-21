<?php
session_start();
$pageClass = "profile-page profile-settings-page";
include_once __DIR__ . "/../includes/header.php";
include_once __DIR__ . "/../includes/topbar.php";
?>
<link rel="stylesheet" href="/SoftEcoms/public/assets/css/profile_page.css">

<main class="container profile-settings-container">
    <header class="settings-page-heading">
        <a class="profile-secondary-link" href="/SoftEcoms/public/users/profilePage.php"><i class="fa-solid fa-arrow-left"></i> Back to Profile</a>
        <span class="profile-view-eyebrow">PREFERENCES</span>
        <h1>Settings</h1>
        <p>Make Sweetclaire feel comfortable for you. Your choices are saved on this device.</p>
    </header>

    <section class="settings-card">
        <div class="settings-option-icon"><i class="fa-solid fa-circle-half-stroke"></i></div>
        <div class="settings-option-copy"><h2>Appearance</h2><p>Choose the look used on your account pages.</p></div>
        <label class="settings-select-label" for="themeChoice">Theme</label>
        <select id="themeChoice" class="form-select">
            <option value="system">Use device setting</option>
            <option value="light">Light</option>
            <option value="dark">Dark</option>
        </select>
    </section>

    <section class="settings-card">
        <div class="settings-option-icon"><i class="fa-solid fa-wand-magic-sparkles"></i></div>
        <div class="settings-option-copy"><h2>Motion</h2><p>Reduce animations and transitions across the site.</p></div>
        <label class="settings-switch" for="reducedMotion"><span>Reduce motion</span><input type="checkbox" id="reducedMotion"><span class="settings-switch-track" aria-hidden="true"></span></label>
    </section>

    <p class="settings-saved" id="settingsSaved" role="status" aria-live="polite">Changes save automatically.</p>
</main>

<?php include_once __DIR__ . "/../includes/footer.php"; ?>
<script>
document.addEventListener("DOMContentLoaded", async () => {
  try {
    const response = await fetch("/api/me", { credentials: "include", headers: { Accept: "application/json" }, cache: "no-store" });
    if (response.status === 401) { window.location.href = "/SoftEcoms/public/login.php"; return; }
  } catch (_) {}

  const themeChoice = document.getElementById("themeChoice");
  const reducedMotion = document.getElementById("reducedMotion");
  const saved = document.getElementById("settingsSaved");
  themeChoice.value = localStorage.getItem("softecoms-theme") || "system";
  reducedMotion.checked = localStorage.getItem("softecoms-reduced-motion") === "true";

  const applyPreferences = () => {
    const theme = themeChoice.value;
    const systemDark = window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches;
    document.documentElement.dataset.siteTheme = theme === "system" ? (systemDark ? "dark" : "light") : theme;
    document.documentElement.dataset.reduceMotion = String(reducedMotion.checked);
    localStorage.setItem("softecoms-theme", theme);
    localStorage.setItem("softecoms-reduced-motion", String(reducedMotion.checked));
    saved.textContent = "Your preferences are saved.";
  };
  themeChoice.addEventListener("change", applyPreferences);
  reducedMotion.addEventListener("change", applyPreferences);
  applyPreferences();
});
</script>
