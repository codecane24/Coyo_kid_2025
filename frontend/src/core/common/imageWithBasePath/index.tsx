import React from 'react';
import { img_path } from '../../../environment';

interface Image {
  className?: string;
  src: string;
  alt?: string;
  height?: number;
  width?: number;
  id?: string;
}

const ImageWithBasePath = (props: Image) => {
  const { src, ...rest } = props;

  // If the src is an absolute URL, use it directly; otherwise, prefix with img_path
  const isExternal = src.startsWith('http') || src.startsWith('data:');
  const fullSrc = isExternal ? src : `${img_path}${src}`;

  return (
    <img
      src={fullSrc}
      {...rest}
    />
  );
};

export default ImageWithBasePath;
