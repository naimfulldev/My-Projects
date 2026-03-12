import { Image } from "antd";
import ImageGallery from "react-image-gallery";
import "react-image-gallery/styles/css/image-gallery.css";

const ProductImages = (props: any) => {
  const { product, imageUrl } = props;

  const gallery = product.images.map((image: any, key: number) => {
    return {
      original: image.src,
      thumbnail: image.src,
      originalAlt: image.name,
      thumbnailAlt: image.name,
    };
  });

  if (product.images.length > 0)
    return (
      <div className="images">
        <ImageGallery
          items={gallery}
          showNav={false}
          showThumbnails={true}
          useTranslate3D={false}
          showBullets={true}
          showIndex={true}
          autoPlay={true}
          slideInterval={5000}
        />
      </div>
    );
  else
    return (
      <Image
        src={imageUrl}
        style={{
          margin: "1rem 0rem",
          cursor: product.images.length ? "pointer" : "",
        }}
        preview={product.images.length <= 0 ? false : true}
      />
    );
};

export default ProductImages;
