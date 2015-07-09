# Y-Designs WordPress Plugin - Location Posts
The Location Posts plugin allows you to... (TODO)

## Usage / Examples
**Required**: From wp-admin, go to Settings -> YD Location Posts and enter your Google Maps API key.

You now have access to a custom post type named `Location Posts`. You can find this on the wp-admin sidebar. Each `Location Post` has several fields including a Google Map with a marker in the center. Move this marker to set a location for your `Location Post`.

Once you have a Published `Location Post`, you can start using the plugins [shortcode](https://codex.wordpress.org/Shortcode_API). The following will show all `Location Posts` on a map!
```
[yd-location-post q='all']
```
or
```
[yd-location-post]
```
Show all posts within a geographical bound
```
[yd-location-post q='bounds' bounds='-34.1,-180,90,180']
```
Show only posts with the given ids
```
[yd-location-post q='post_ids' post_ids='23, 25']
```


Available paramets:
- q: 'all', 'bounds', 'post_ids'
- bounds: (Comma seperated list of coordinates in format sw_lat, sw_lng, ne_lat, ne_lng. Ex: '-34.1,-180,90,180' )
- post_ids: (Comma seperated list of `Location Post` ids. Ex: '23,25,29' )
- location_taxonomies: (Comma seperated list of `Location Taxonomy` slugs. Ex: 'seattle-region,sanfransisco-region') (assuming those Location Taxonmies were created!)
