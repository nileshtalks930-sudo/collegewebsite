/**
 * Shared TinyMCE integration for College CMS.
 * Features: tables, images, links, video/media, code/source, file browser, image upload.
 */
(function (window) {
  const cfg = window.CollegeCmsEditorConfig || {};

  let pendingPickerCallback = null;

  function baseConfig(selector, overrides) {
    return Object.assign({
      selector: selector,
      height: overrides?.height || 420,
      menubar: 'file edit view insert format tools table',
      plugins:
        'advlist autolink lists link image charmap preview anchor ' +
        'searchreplace visualblocks code fullscreen insertdatetime media table ' +
        'wordcount codesample',
      toolbar:
        'undo redo | styles | bold italic underline strikethrough | ' +
        'alignleft aligncenter alignright alignjustify | ' +
        'bullist numlist outdent indent | ' +
        'link image media table | ' +
        'preview code fullscreen',
      toolbar_mode: 'wrap',
      branding: false,
      promotion: false,
      convert_urls: false,
      relative_urls: false,
      remove_script_host: false,
      image_title: true,
      image_caption: true,
      automatic_uploads: true,
      images_reuse_filename: false,
      file_picker_types: 'file image media',
      media_live_embeds: true,
      content_style:
        'body { font-family: Source Sans 3, Helvetica, Arial, sans-serif; font-size: 15px; }' +
        ' img { max-width: 100%; height: auto; }' +
        ' table { border-collapse: collapse; width: 100%; }' +
        ' table td, table th { border: 1px solid #ccc; padding: 6px; }',
      images_upload_handler: function (blobInfo) {
        return new Promise(function (resolve, reject) {
          if (!cfg.uploadUrl) {
            reject('Image upload URL is not configured.');
            return;
          }
          const formData = new FormData();
          formData.append('file', blobInfo.blob(), blobInfo.filename());
          formData.append('_token', cfg.csrfToken || '');

          fetch(cfg.uploadUrl, {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': cfg.csrfToken || '',
              Accept: 'application/json',
            },
            body: formData,
            credentials: 'same-origin',
          })
            .then(function (res) {
              return res.json().then(function (json) {
                return { ok: res.ok, json: json };
              });
            })
            .then(function (result) {
              if (result.json && result.json.location) {
                resolve(result.json.location);
              } else {
                reject((result.json && result.json.error) || 'Image upload failed.');
              }
            })
            .catch(function () {
              reject('Image upload failed.');
            });
        });
      },
      file_picker_callback: function (callback, value, meta) {
        if (!cfg.pickerUrl) {
          return;
        }
        pendingPickerCallback = callback;
        const type = meta.filetype || 'file';
        const url = cfg.pickerUrl + (cfg.pickerUrl.indexOf('?') >= 0 ? '&' : '?') + 'type=' + encodeURIComponent(type);
        window.open(url, 'CollegeCmsFileBrowser', 'width=980,height=700,resizable=yes,scrollbars=yes');
      },
    }, overrides || {});
  }

  const api = {
    init: function (selector, overrides) {
      if (!window.tinymce) {
        console.error('TinyMCE is not loaded');
        return;
      }
      window.tinymce.init(baseConfig(selector, overrides || {}));
    },
    initAll: function (selector) {
      api.init(selector || 'textarea.cms-editor');
    },
    onFilePicked: function (payload) {
      if (typeof pendingPickerCallback !== 'function') {
        return;
      }
      const url = payload.url;
      const name = payload.name || '';
      pendingPickerCallback(url, {
        text: name,
        alt: name,
        title: name,
      });
      pendingPickerCallback = null;
    },
  };

  window.CollegeCmsEditor = api;

  document.addEventListener('DOMContentLoaded', function () {
    if (document.querySelector('textarea.cms-editor') && window.tinymce) {
      api.initAll('textarea.cms-editor');
    }
  });
})(window);
