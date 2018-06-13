# yamaps
YaMaps plugin is the simplest way to insert Yandex maps on your WordPress site. This is extended version of the plugin.

Base description of the original plugin can be found here [link](https://wordpress.org/plugins/yamaps/#description).

---

This fork contents additional features:

  * added attribute _auto_zoom_ to the tag **[yamap]**. If it set to not null value, result map will be zoomed automaticly for all the placemarks (attributes _center_ and _zoom_ will be ignored);
  * added attribute _balloon_ to the tag **[yaplacemark]**. It's holds the content for the pop up balloon (may contain HTML tags);
  * HTML content is enabled for attributes;
  * plugin code is totally refactored;
  * removed attribute _url_ of the **[yaplacemark]** tag.
