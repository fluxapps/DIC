Use all ILIAS globals in your class

### Usage

#### Composer
First add the following to your `composer.json` file:
```json
"require": {
  "srag/dic": ">=0.1.0"
},
```
And run a `composer install`.

If you deliver your plugin, the plugin has it's own copy of this library and the user doesn't need to install the library.

Tip: Because of multiple autoloaders of plugins, it could be, that different versions of this library exists and suddenly your plugin use an older or a newer version of an other plugin!

So I recommand to use [srag/librariesnamespacechanger](https://packagist.org/packages/srag/librariesnamespacechanger) in your plugin.

#### Use trait
Declare your class like follow:
```php
//...
use srag\DIC\DICTrait;
//...
class x {
	//...
	use DICTrait;
	//...
	const PLUGIN_CLASS_NAME = ilXPlugin::class;
	//...
}
```
`ilXPlugin` is the name of your plugin class.

#### Use
You can now access the DIC interface, in instance and in static places:
```php
/**
 * Get DIC interface
 * 
 * @return DICInterface DIC interface
 */
self::dic(): DICInterface;
```

For instance you can access the ilCtrl global like:
```php
/**
 * @return ilCtrl
 */
self::dic()->ctrl(): ilCtrl;
```

You can access the plugin interface, in instance and in static places:
```php
/**
 * Get plugin interface
 * 
 * @return PluginInterface Plugin interface
 *
 * @throws DICException
 */
self::plugin(): PluginInterface;
```

The plugin interface has the follow methods:

For plugin dir use:
```php
/**
 * Get plugin directory
 * 
 * @return string Plugin directory
 */
self::plugin()->directory(): string;
```

For output HTML or GUI use:
```php
/**
 * Output HTML or GUI
 * 
 * @param string|object $html          HTML code or some GUI instance
 * @param bool          $show          Show main template?
 * @param bool          $main_template Display main skin?
 *
 * @throws DICException
 */
self::output()->output($value, bool $show = false, bool $main_template = true)/*: void*/;
```

For output JSON:
```php
/**
 * Output JSON
 * 
 * @param string|int|double|bool|array|stdClass|null|JsonSerializable $value JSON value
 *
 * @throws DICException
 */
self::output()->outputJSON($value)/*: void*/;
```

For get HTML of GUI:
```php
/**
 * Get HTML of GUI
 * 
 * @param string|object $html HTML code or some GUI instance
 *
 * @return string HTML
 *
 * @throws DICException
 */
self::output()->getHTML($value): string;
```

For get a template use:
```php
/**
 * Get a template
 * 
 * @param string $template                 Template path
 * @param bool   $remove_unknown_variables Should remove unknown variables?
 * @param bool   $remove_empty_blocks      Should remove empty blocks?
 * @param bool   $plugin                   Plugin template or ILIAS core template?
 *
 * @return ilTemplate ilTemplate instance
 *
 * @throws DICException
 */
self::plugin()->template(string $template, bool $remove_unknown_variables = true, bool $remove_empty_blocks = true, bool $plugin = true): ilTemplate;
```

For translate use:
```php
/**
 * Translate text
 * 
 * @param string $key          Language key
 * @param string $module       Language module
 * @param array  $placeholders Placeholders in your language texst to replace with vsprintf
 * @param bool   $plugin       Plugin language or ILIAS core language?
 * @param string $lang         Possibly specific language, otherwise current language, if empty
 * @param string $default      Default text, if language key not exists
 *
 * @return string Translated text
 *
 * @throws DICException
 */
self::plugin()->translate(string $key, string $module = "", array $placeholders = [], bool $plugin = true, string $lang = "", string $default = "MISSING %s"): string;
```
Hints:
- Please use not more manually `sprintf` or `vsprintf`, use the `$placeholders` parameter. Otherwise you will get an appropriate DICException thrown. This because `translate` use always `vsprintf` and if you pass to few palceholders, `vsprintf` will throw an Exception.
- Because `translate` use `vsprintf`, you need to escape `%` with `%%` in your language strings if it is no placeholder!

If you really need the ILIAS plugin object use but avoid this:
```php
/**
 * Get ILIAS plugin object instance
 *
 * Please avoid to use ILIAS plugin object instance and instead use methods in this class!
 *
 * @return ilPlugin ILIAS plugin object instance
 */
self::plugin()->getPluginObject(): ilPlugin;
```

You can access ILIAS version informations, in instance and in static places:
```php
/**
 * Get version interface
 * 
 * @return VersionInterface Version interface
 */
self::version(): VersionInterface;
```

If you really need DICTrait outside a class (For instance in `dbupdate.php`), use `DICStatic::dic()` or `DICStatic::plugin(ilXPlugin::class)`.

#### Clean up
You can now remove all usages of ILIAS globals in your class and replace it with this library.
Please avoid to store in variables or class variables.

#### LibraryLanguageInstaller
Expand you plugin class for installing languages of a library to your plugin
```php
...
	/**
     * @inheritDoc
     */
    public function updateLanguages(/*?array*/ $a_lang_keys = null)/*:void*/ {
		parent::updateLanguages($a_lang_keys);

		LibraryLanguageInstaller::getInstance()->withPlugin(self::plugin())->withLibraryLanguageDirectory(__DIR__ . "/../vendor/srag/x/lang")
			->updateLanguages($a_lang_keys);
	}
...
```

#### Database
This library delivers also a custom `ilDB` decorator class with spec. functions, restricted to `PDO` (Because to make access more core functions), access via `self:.dic()->database()`

If you realy need to access to original ILIAS `ilDB` instance, use `self:.dic()->databaseCore()` instead

##### Native AutoIncrement (MySQL) / Native Sequence (PostgreSQL)
Use auto increment on a spec. field (in `dbupdate.php`):
```php
\srag\DIC\x\DICStatic::dic()->database()->createAutoIncrement(\srag\Plugins\x\x\x::TABLE_NAME, "id");
```

Reset auto increment:
```php
self::dic()->database()->resetAutoIncrement(x::TABLE_NAME, "id");
```

Drop auto increment table (Needed for PostgreSQL) (in `ilXPlugin` uninstaller):
```php
self::dic()->database()->dropAutoIncrementTable(x::TABLE_NAME);
```

##### Store (In repository)
```php
$x = $this->factory()->newInstance();
...
$x->setId(self::dic()->database()->store(x::TABLE_NAME, [
			"field_1" => [ ilDBConstants::T_TEXT, $x->getField1() ],
			"field_2" => [ ilDBConstants::T_INTEGER, $x->getField2() ]
		], "id", $x->getId()));
```

##### Automatic factory (In repository)
```php
$array = self::dic()->database()->fetchAllCallback(self::dic()->database()->query('SELECT * FROM ' . self::dic()->database()
				->quoteIdentifier(x::TABLE_NAME)), [ $this->factory(), "fromDB" ]);
		
...

public function fromDB(stdClass $data): x {
	$x = $this->newInstance();

	$x->setId($data->id);
	$x->setField1($data->field_1);
	$x->setField2($data->field_2);

	return $x;
}

```

### Requirements
* ILIAS 5.3 or ILIAS 5.4 or ILIAS 6
* PHP >=7.0
* PDO (MySQL or PostgreSQL 9.5)

### Adjustment suggestions
* External users can report suggestions and bugs at https://plugins.studer-raimann.ch/goto.php?target=uihk_srsu_LDIC
* Adjustment suggestions by pull requests via github
* Customer of studer + raimann ag: 
	* Adjustment suggestions which are not yet worked out in detail by Jira tasks under https://jira.studer-raimann.ch/projects/LDIC
	* Bug reports under https://jira.studer-raimann.ch/projects/LDIC
