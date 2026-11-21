const fs = require("fs/promises");
const path = require("path");
const { put, del } = require("@vercel/blob");

const DEFAULT_PROFILE_IMAGE = "default-avatar.svg";

function resolveImageUrl(value, legacyPrefix) {
  const image = String(value || "").trim();
  if (/^https?:\/\//i.test(image)) return image;
  return legacyPrefix + encodeURIComponent(image || DEFAULT_PROFILE_IMAGE);
}

async function storePublicImage({ buffer, filename, folder, localDirectory }) {
  if (process.env.BLOB_READ_WRITE_TOKEN) {
    const blob = await put(`${folder}/${filename}`, buffer, {
      access: "public",
      token: process.env.BLOB_READ_WRITE_TOKEN,
    });
    return { reference: blob.url, localPath: null };
  }

  if (process.env.VERCEL) {
    throw new Error("Image storage is not configured. Add BLOB_READ_WRITE_TOKEN in Vercel.");
  }

  await fs.mkdir(localDirectory, { recursive: true });
  const localPath = path.join(localDirectory, filename);
  await fs.writeFile(localPath, buffer, { flag: "wx" });
  return { reference: filename, localPath };
}

async function removePublicImage(reference, localDirectory) {
  const image = String(reference || "").trim();
  if (!image || image === DEFAULT_PROFILE_IMAGE) return;

  if (/^https?:\/\//i.test(image)) {
    if (process.env.BLOB_READ_WRITE_TOKEN) {
      await del(image, { token: process.env.BLOB_READ_WRITE_TOKEN }).catch((error) => {
        console.warn("Could not remove stored image:", error.message);
      });
    }
    return;
  }

  if (path.basename(image) !== image) return;
  await fs.unlink(path.join(localDirectory, image)).catch(() => {});
}

module.exports = { resolveImageUrl, storePublicImage, removePublicImage };
