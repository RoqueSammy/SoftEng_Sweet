<?php
session_start();
$pageClass = "profile-page profile-view-page";
include_once __DIR__ . "/../includes/header.php";
include_once __DIR__ . "/../includes/topbar.php";
?>
<link rel="stylesheet" href="/SoftEcoms/public/assets/css/profile_page.css">

<main class="container profile-view-container">
    <section class="profile-view-card">
        <img id="profilePhoto" src="/SoftEcoms/public/assets/profile_img/default-avatar.svg" alt="Profile photo">
        <span class="profile-view-eyebrow">MY ACCOUNT</span>
        <h1 id="profileName">Loading your profile…</h1>
        <p id="profileEmail" class="profile-view-email"></p>
        <a class="profile-primary-link" href="/SoftEcoms/public/users/editProfile.php"><i class="fa-solid fa-pen"></i> Edit profile</a>
        <a class="profile-secondary-link" href="/SoftEcoms/public/users/settings.php"><i class="fa-solid fa-gear"></i> Account settings</a>
    </section>

    <section class="profile-info-card">
        <div class="profile-info-heading"><div><span class="profile-view-eyebrow">YOUR DETAILS</span><h2>Personal information</h2></div></div>
        <dl class="profile-info-grid">
            <div><dt>First name</dt><dd id="profileFirstName">—</dd></div>
            <div><dt>Last name</dt><dd id="profileLastName">—</dd></div>
            <div><dt>Mobile number</dt><dd id="profilePhone">—</dd></div>
            <div><dt>Email address</dt><dd id="profileEmailInfo">—</dd></div>
            <div><dt>Street</dt><dd id="profileStreet">—</dd></div>
            <div><dt>Barangay</dt><dd id="profileBarangay">—</dd></div>
            <div><dt>City</dt><dd id="profileCity">—</dd></div>
        </dl>
        <a class="profile-inline-edit" href="/SoftEcoms/public/users/editProfile.php">Update your information <i class="fa-solid fa-arrow-right"></i></a>
    </section>
</main>

<script>
document.addEventListener("DOMContentLoaded", async () => {
  try {
    const response = await fetch("/api/me", { credentials: "include", headers: { Accept: "application/json" }, cache: "no-store" });
    if (response.status === 401) { window.location.href = "/SoftEcoms/public/login.php"; return; }
    if (!response.ok) throw new Error("Could not load your profile.");
    const data = await response.json();
    const user = data.user || {};
    const fullName = [user.firstName, user.lastName].filter(Boolean).join(" ") || user.username || "Sweetclaire customer";
    document.getElementById("profileName").textContent = fullName;
    document.getElementById("profileEmail").textContent = user.emailAddress || "";
    const image = document.getElementById("profilePhoto");
    image.src = user.profileImageUrl || (user.profile ? "/SoftEcoms/public/assets/profile_img/" + encodeURIComponent(user.profile) : "/SoftEcoms/public/assets/profile_img/default-avatar.svg");
    image.onerror = () => { image.onerror = null; image.src = "/SoftEcoms/public/assets/profile_img/default-avatar.svg"; };
    const fields = { profileFirstName: user.firstName, profileLastName: user.lastName, profilePhone: user.contactNumber, profileEmailInfo: user.emailAddress, profileStreet: user.street, profileBarangay: user.barangay, profileCity: user.city };
    Object.entries(fields).forEach(([id, value]) => { document.getElementById(id).textContent = value || "Not provided"; });
  } catch (error) {
    document.getElementById("profileName").textContent = "Profile unavailable";
    document.getElementById("profileEmail").textContent = error.message;
  }
});
</script>
