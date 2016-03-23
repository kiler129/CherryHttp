# WIP
Master tree (where v2 development is made) is NOT intended to general usage just yet. If you're looking for stable release switch to [v1](https://github.com/kiler129/CherryHttp/tree/1.0) tree, which is not obsoleted in any way.

There'll be no examples until v2 enters alpha state.

# Old examples note
 All examples assumes composer installation. If you installed CherryHttp using different method you need to modify examples code to include necessary files.  
`_RequiredFiles.php` contains necessary includes to run them without autoloader present - just replace `require_once('../../../autoload.php')` with `require_once('_RequiredFiles.php')`.  

Examples also uses [Shout](https://github.com/kiler129/Shout) as log target. You can replace it with any [PSR-3 complaint logger](https://packagist.org/search/?tags=psr-3) or just pass NullLogger() to run in complete salience.
