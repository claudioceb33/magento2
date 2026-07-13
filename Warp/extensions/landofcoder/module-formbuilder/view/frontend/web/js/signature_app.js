define([
    "jquery",
    "lofFieldDigitalSignature"
], function($, SignaturePad) {
    "use strict";
    $.widget("lof.signatureApp", {
        options: {},
        _create: function() {
            var self = this;
            const selectorName = self.options.elementId
            const fieldId = self.options.fieldId
            const downloadFileType = String(self.options.downloadFileType).toLowerCase() || "png"
            const backgroundColor = self.options.backgroundColor || 'rgba(0,0,0,0)'
            const penColor = self.options.penColor || 'rgb(0, 0, 0)'
            if (typeof(window.signaturePads) == "undefined") {
                window.signaturePads = []
            }
            if($(selectorName).length > 0 && $(fieldId).length > 0){
                const wrapper = $(selectorName);
                var inputField = $(fieldId);
                var canvas = wrapper.find("canvas")[0];
                var clearButton = wrapper.find("[data-action=clear]")[0];
                var undoButton = wrapper.find("[data-action=undo]")[0];
                var confirmSignatureCheckbox = wrapper.find("[data-action=confirmSignature]")[0];
                var signaturePad = new SignaturePad(canvas, {
                    // It's Necessary to use an opaque color when saving image as JPEG;
                    // this option can be omitted if only saving as PNG or SVG
                    backgroundColor: backgroundColor,
                    penColor: penColor
                });

                signaturePads.push(signaturePad)

                clearButton.addEventListener("click", (event) => {
                    signaturePad.clear();
                    $(confirmSignatureCheckbox).prop('checked', false);
                    $(confirmSignatureCheckbox).trigger("change");
                });

                undoButton.addEventListener("click", (event) => {
                    var data = signaturePad.toData();

                    if (data) {
                      data.pop(); // remove the last dot or line
                      signaturePad.fromData(data);
                    }
                });

                $(confirmSignatureCheckbox).change((event) => {
                    if (event.currentTarget.checked) {
                      const signatureFileUrl = downloadSignature()
                      inputField.val(signatureFileUrl)
                    } else {
                        inputField.val("")
                    }
                })

                function downloadSignature(){
                    if (signaturePad.isEmpty()) {
                        alert("Please provide a signature first.");
                    } else {
                        let dataURL = ""
                        switch(downloadFileType){
                            case "jpg":
                            case "jpeg":
                                dataURL = signaturePad.toDataURL("image/jpeg");
                                break;
                            case "svg":
                                dataURL = signaturePad.toDataURL('image/svg+xml');
                                break;
                            case "png":
                            default:
                                dataURL = signaturePad.toDataURL();
                                break;
                        }
                        return confirmSignatureUrl(dataURL);
                    }
                }
                function confirmSignatureUrl(dataURL) {
                    return dataURL
                    if (navigator.userAgent.indexOf("Safari") > -1 && navigator.userAgent.indexOf("Chrome") === -1) {
                      return dataURL;
                    } else {
                      const blob = lofDataURLToBlob(dataURL);
                      const url = window.URL.createObjectURL(blob);
                      return url
                    }
                }

                function lofDataURLToBlob (dataURL) {
                    // Code taken from https://github.com/ebidel/filer.js
                    var parts = dataURL.split(';base64,');
                    var contentType = parts[0].split(":")[1];
                    var raw = window.atob(parts[1]);
                    var rawLength = raw.length;
                    var uInt8Array = new Uint8Array(rawLength);

                    for (var i = 0; i < rawLength; ++i) {
                      uInt8Array[i] = raw.charCodeAt(i);
                    }

                    return new Blob([uInt8Array], { type: contentType });
                }

                function resizeCanvas(canvas, signaturePad) {
                    // When zoomed out to less than 100%, for some very strange reason,
                    // some browsers report devicePixelRatio as less than 1
                    // and only part of the canvas is cleared then.
                    var ratio =  Math.max(window.devicePixelRatio || 1, 1);

                    // This part causes the canvas to be cleared
                    canvas.width = canvas.offsetWidth * ratio;
                    canvas.height = canvas.offsetHeight * ratio;
                    canvas.getContext("2d").scale(ratio, ratio);

                    // This library does not listen for canvas changes, so after the canvas is automatically
                    // cleared by the browser, SignaturePad#isEmpty might still return false, even though the
                    // canvas looks empty, because the internal data of this library wasn't cleared. To make sure
                    // that the state of this library is consistent with visual state of the canvas, you
                    // have to clear it manually.
                    signaturePad.clear();
                }

                window.onresize = resizeCanvas(canvas, signaturePad);
                window.clearDigitalSignature = () => {
                    window.signaturePads.forEach((itemPad) => {
                        itemPad.clear();
                    })
                    signaturePad.clear();
                }
                resizeCanvas(canvas, signaturePad);
            }

        }
    });
    return $.lof.signatureApp;
});
