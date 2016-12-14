var common = require("./image-asset-common");
global.moduleMerge(common, exports);
var ImageAsset = (function (_super) {
    __extends(ImageAsset, _super);
    function ImageAsset(asset) {
        _super.call(this);
        if (asset instanceof UIImage) {
            this.nativeImage = asset;
        }
        else {
            this.ios = asset;
        }
    }
    ImageAsset.prototype.getImageAsync = function (callback) {
        var requestedSize = common.getRequestedImageSize({
            width: this.nativeImage ? this.nativeImage.size.width : this.ios.pixelWidth,
            height: this.nativeImage ? this.nativeImage.size.height : this.ios.pixelHeight
        });
        if (this.nativeImage) {
            var newSize = CGSizeMake(requestedSize.width, requestedSize.height);
            UIGraphicsBeginImageContextWithOptions(newSize, false, 0.0);
            this.nativeImage.drawInRect(CGRectMake(0, 0, newSize.width, newSize.height));
            var resizedImage = UIGraphicsGetImageFromCurrentImageContext();
            UIGraphicsEndImageContext();
            callback(resizedImage, null);
            return;
        }
        var imageRequestOptions = PHImageRequestOptions.alloc().init();
        imageRequestOptions.deliveryMode = 1;
        PHImageManager.defaultManager().requestImageForAssetTargetSizeContentModeOptionsResultHandler(this.ios, requestedSize, 0, imageRequestOptions, function (image, imageResultInfo) {
            if (image) {
                callback(image, null);
            }
            else {
                callback(null, imageResultInfo.valueForKey(PHImageErrorKey));
            }
        });
    };
    return ImageAsset;
}(common.ImageAsset));
exports.ImageAsset = ImageAsset;
//# sourceMappingURL=image-asset.js.map