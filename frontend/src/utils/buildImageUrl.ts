const BASE_URL = process.env.REACT_APP_BASE_URL;


export const buildImageUrl = (imgPath: string): string => {
  if (!imgPath) return "";
  if (imgPath.startsWith("https://coyokid.abbangles.com/backend/storage/"))
    return imgPath;
  return `${BASE_URL}/${imgPath}`;
};
