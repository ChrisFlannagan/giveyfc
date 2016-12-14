var platform = require("platform");
var ImageAsset = (function () {
    function ImageAsset() {
    }
    Object.defineProperty(ImageAsset.prototype, "options", {
        get: function () {
            return this._options;
        },
        set: function (value) {
            this._options = value;
        },
        enumerable: true,
        configurable: true
    });
    Object.defineProperty(ImageAsset.prototype, "ios", {
        get: function () {
            return this._ios;
        },
        set: function (value) {
            this._ios = value;
        },
        enumerable: true,
        configurable: true
    });
    Object.defineProperty(ImageAsset.prototype, "android", {
        get: function () {
            return this._android;
        },
        set: function (value) {
            this._android = value;
        },
        enumerable: true,
        configurable: true
    });
    Object.defineProperty(ImageAsset.prototype, "nativeImage", {
        get: function () {
            return this._nativeImage;
        },
        set: function (value) {
            this._nativeImage = value;
        },
        enumerable: true,
        configurable: true
    });
    ImageAsset.prototype.getImageAsync = function (callback) {
    };
    return ImageAsset;
}());
exports.ImageAsset = ImageAsset;
function getAspectSafeDimensions(sourceWidth, sourceHeight, reqWidth, reqHeight) {
    var widthCoef = sourceWidth / reqWidth;
    var heightCoef = sourceHeight / reqHeight;
    var aspectCoef = widthCoef > heightCoef ? widthCoef : heightCoef;
    return {
        width: Math.floor(sourceWidth / aspectCoef),
        height: Math.floor(sourceHeight / aspectCoef)
    };
}
exports.getAspectSafeDimensions = getAspectSafeDimensions;
function getRequestedImageSize(src) {
    var reqWidth = platform.screen.mainScreen.widthDIPs;
    var reqHeight = platform.screen.mainScreen.heightDIPs;
    if (this.options && this.options.width) {
        reqWidth = (this.options.width > 0 && this.options.width < reqWidth) ? this.options.width : reqWidth;
    }
    if (this.options && this.options.height) {
        reqWidth = (this.options.height > 0 && this.options.height < reqHeight) ? this.options.height : reqHeight;
    }
    if (this.options && this.options.keepAspectRatio) {
        var safeAspectSize = getAspectSafeDimensions(src.width, src.height, reqWidth, reqHeight);
        reqWidth = safeAspectSize.width;
        reqHeight = safeAspectSize.height;
    }
    return {
        width: reqWidth,
        height: reqHeight
    };
}
exports.getRequestedImageSize = getRequestedImageSize;
//# sourceMappingURL=image-asset-common.js.map