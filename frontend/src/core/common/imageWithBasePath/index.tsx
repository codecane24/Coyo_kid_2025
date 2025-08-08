import React from 'react';
import { img_path } from '../../../environment';

interface Image {
  className?: string;
  src: string;
  alt?: string;
  height?: number;
  width?: number;
  id?: string;
  isApiImage?: boolean; // ✅ New prop
}

const ImageWithBasePath = (props: Image) => {
  const { src, isApiImage, ...rest } = props;

  const isExternal = src.startsWith('http') || src.startsWith('data:');

  const fullSrc = isExternal
    ? src
    : isApiImage
      ? `${img_path}/${src}`
      : src; // Just use as-is for local public assets

  return <img src={fullSrc} {...rest} />;
};

export default ImageWithBasePath;
