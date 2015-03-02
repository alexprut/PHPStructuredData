PHPStructuredData [![Build Status](https://travis-ci.org/PAlexcom/PHPStructuredData.svg)](https://travis-ci.org/PAlexcom/PHPStructuredData)
=================
A set of PHP libraries that use the http://schema.org vocabulary to implement and output Microdata or RDFa Lite 1.1 semantics.  
This library is used in the Joomla CMS since version 3.2 (called [JMicrodata](https://github.com/joomla/joomla-cms/tree/master/libraries/joomla/microdata "JMicrodata")).  
Created during the Google Summer of Code 2013 and 2014.

Quick overview
--------------
The library was designed with this goals in mind:  
1. Having the possibility to __switch between Microdata__ and __RDFa Lite 1.1__ semantics.  
2. Having the possibility to __switch the Type dynamically__, you just change the Type (there are more than 550+ different available Types).  
3. Display  __validated semantics__, the library takes care of displaying data in the correct format (e.g. all the dates in the ISO standard).  
4. __Enable/disable__ the __library__ output.  
5. __Fallbacks__, you should never lose any meaningful semantic (e.g. if you change the Type and it does not have an _author_ Property, it will fallback to the _Person_ Type with the _name_ Property).  

Class diagram
-------------
![Class Diagram](https://palexcom.github.io/PHPStructuredData/images/classdiagram-v2.0.0.png)

Installation
------------
* __Composer__:  
    Add in your ```composer.json``` file:

    ``` json
    {
        "require": {
            "palex/phpstructureddata": "*"
        }
    }
    ```
* __From Source__:  
    Run ```git clone https://github.com/PAlexcom/PHPStructuredData.git```
* __Direct download__:  
    Download the last version from [here](https://github.com/PAlexcom/PHPStructuredData/archive/master.zip "download")

Usage Example
-------------
Let's suppose that you already have an instance of the Microdata or RDFa library. And you need to add Microdata or RDFa semantics to the following HTML which is part of an article (_e.g._ ```$sd = new PHPStructuredData\Microdata('Article');```).
```php
<div <?php echo $sd->displayScope();?>>
    <!-- Language -->
    <?php echo $sd->content(null, 'en-GB')->property('inLanguage')->display('meta', true)?>
    <!-- Title -->
    <?php echo $sd->content('How to Tie a Reef Knot')->property('name')->display();?>
	<!-- Author-->
    <span>
    	Written by <?php echo $sd->content('John Doe')->property('author')->fallback('Person', 'name')->display();?>
    </span>
    <!-- Date published -->
    <?php echo $sd->content('1 January 2014', '2014-01-01T00:00:00+00:00')->property('datePublished')->display();?>
    <!-- Content -->
    <?php echo $sd->content('Lorem ipsum dolor sit amet...')->property('articleBody')->display();?>
<div>
```
The ```Microdata``` library will render:
```html
<div itemscope itemtype='https://schema.org/Article'>
    <!-- Language -->
    <meta itemprop='inLanguage' content='en-GB'/>
    <!-- Title -->
    <span itemprop='name'>
        How to Tie a Reef Knot
    </span>
    <!-- Author -->
    <span>
    	Written by
        <span itemprop='author' itemscope itemtype='https://schema.org/Person'>
            <span itemprop='name'>John Doe</span>
        </span>
    </span>
    <!-- Date published -->
    <meta itemprop='datePublished' content='2014-01-01T00:00:00+00:00'/>1 January 2014
    <!-- Content -->
    <span itemprop='articleBody'>
        Lorem ipsum dolor sit amet...
    </span>
<div>
```
The ```RDFa``` library will render:
```html
<div vocab='https://schema.org' typeof='Article'>
    <!-- Language -->
    <meta property='inLanguage' content='en-GB'/>
    <!-- Title -->
    <span property='name'>
        How to Tie a Reef Knot
    </span>
    <!-- Author -->
    <span>
    	Written by
        <span property='author' vocab='https://schema.org' typeof='Person'>
            <span property='name'>John Doe</span>
        </span>
    </span>
    <!-- Date published -->
    <meta property='datePublished' content='2014-01-01T00:00:00+00:00'/>1 January 2014
    <!-- Content -->
    <span property='articleBody'>
        Lorem ipsum dolor sit amet...
    </span>
<div>
```
Instead, if you decide to change the current Type (_e.g._ ```$sd->setType('Review');```).  
The ```Microdata``` library will render:
```html
<div itemscope itemtype='https://schema.org/Review'>
    <!-- Language -->
    <meta itemprop='inLanguage' content='en-GB'/>
    <!-- Title -->
    <span itemprop='name'>
        How to Tie a Reef Knot
    </span>
    <!-- Author -->
    <span>
    	Written by
        <span itemscope itemtype='https://schema.org/Person'>
            <span itemprop='name'>John Doe</span>
        </span>
    </span>
    <!-- Date published -->
    <meta itemprop='datePublished' content='2014-01-01T00:00:00+00:00'/>1 January 2014
    <!-- Content -->
    Lorem ipsum dolor sit amet...
<div>
```
The ```RDFa``` library will render:
```html
<div vocab='https://schema.org' typeof='Review'>
    <!-- Language -->
    <meta property='inLanguage' content='en-GB'/>
    <!-- Title -->
    <span property='name'>
        How to Tie a Reef Knot
    </span>
    <!-- Author -->
    <span>
    	Written by
        <span vocab='https://schema.org' typeof='Person'>
            <span property='name'>John Doe</span>
        </span>
    </span>
    <!-- Date published -->
    <meta property='datePublished' content='2014-01-01T00:00:00+00:00'/>1 January 2014
    <!-- Content -->
    Lorem ipsum dolor sit amet...
<div>
```
As you can see ```John Doe``` __fallbacks__ to the _Person_ Type, and there is no loss of information, even if the current Type doesn't have an _author_ Property it will display important information for the machines, search engines know that there is a Person ```John Doe```.  
Instead, if you decide to not render Microdata or RDFa semantics, you just __disable the library__ output (_e.g._ ```$sd->enable('false');```).  
Both ```Microdata``` and ```RDFa``` library will render:
```html
<div >
    <!-- Language -->
    <!-- Title -->
    How to Tie a Reef Knot
	<!-- Author-->
    <span>
    	Written by John Doe
    </span>
    <!-- Date published -->
    1 January 2014
    <!-- Content -->
    Lorem ipsum dolor sit amet...
<div>
```
Currently both ```RDFa``` and ```Microdata``` library doesn't support multiple fallbacks.

ParserPlugin
============
If you want to keep your views separated from the logic, ```ParserPlugin``` is a PHP class for parsing the HTML markup and converting the ```data-*``` HTML5 attributes into the correctly formatted Microdata or RDFa Lite 1.1 semantics.  

The ```data-*``` attributes are new in HTML5, they gives us the ability to embed custom data attributes on all HTML elements. So if you disable the library output, the HTML will still be validated. The default suffix the library will search for is ```data-sd```, where sd stands for structured data, but you can register more than one custom suffix.   
   
Markup Syntax
-------------
##### setType
![ParserPlugin Syntax](https://palexcom.github.io/PHPStructuredData/images/parser-plugin-syntax-v1.3.0-setType.png)  
The _type_ defines which schema is being used for the following markup.  The Type must always have the first character Uppercase to be correctly interpreted. If the type is a valid schema, the global scope for the page from this point onwards is updated to this schema. The plugin will replace the data tag with ```itemscope itemtype='https://schema.org/Type'``` in case of Microdata semantics or ```vocab='https://schema.org' typeof='Type'``` in case of RDFa Lite 1.1 semantics.  
  
###### Example:
```html
<div data-sd="Article">
    <p>This is my article</p>
</div>
```

This will be output using ```Microdata``` semantics as:
```html
<div itemscope itemtype="http://schema.org/Article">
    <p>This is my article</p>
</div>
```
Or using ```RDFa``` semantics as:
```html
<div vocab="http://schema.org" typeof="Article">
    <p>This is my article</p>
</div>
```

##### Specifying generic item properties
![ParserPlugin Syntax](https://palexcom.github.io/PHPStructuredData/images/parser-plugin-syntax-v1.3.0-global.png)  
Once a schema has been declared, the next step is to declare individual properties – explaining the content and giving it semantic meaning.
  
The _property_ must always have the first character as lowercase to be correctly interpreted. If the property is found to be part of the current schema, the plugin will replace the data tag with ```itemprop='property'``` in case of Microdata semantics or ```property='property'``` in case of RDFa Lite 1.1 semantics.  If the property is not found to be a valid property of the active schema, it will be ignored and the next available property will be parsed.

###### Example:
```html
<div data-sd="Article">
    <p data-sd="articleBody">This is my article</p>
</div>
```

This will be output using ```Microdata``` semantics as:
```html
<div itemscope itemtype="http://schema.org/Article">
    <p itemprop="articleBody">This is my article</p>
</div>
```
Or using ```RDFa``` semantics as:
```html
<div vocab="http://schema.org" typeof="Article">
    <p property="articleBody">This is my article</p>
</div>
```

##### Specifying schema—dependant item properties
![ParserPlugin Syntax](https://palexcom.github.io/PHPStructuredData/images/parser-plugin-syntax-v1.3.0-specialized.png)  
Sometimes you may want to explicitly state a property which should only be used when a specific schema is active – for example, if the property has a specific property in one schema, which is called something different in another schema.

It is possible to achieve this by using a schema–dependant property.  This works by using a combination between both _Type_ and _property_, separated by a full stop. In short, if the current global scope is equal to Type and the property is part of that Type, the plugin will replace the data tag with ```itemprop='property'``` in case of Microdata semantics or ```property='property'``` in case of RDFa Lite 1.1.

###### Example:
```html
<div data-sd="Article">
    <p data-sd="articleBody">This is my article</p>
    <p data-sd="Article.wordcount">4</p>
</div>
```

This will be output using ```Microdata``` semantics as:
```html
<div itemscope itemtype="http://schema.org/Article">
    <p itemprop="articleBody">This is my article</p>
    <p itemprop="wordcount">4</p>
</div>
```
Or using ```RDFa``` semantics as:
```html
<div vocab="http://schema.org" typeof="Article">
    <p property="articleBody">This is my article</p>
    <p property="wordcount">4</p>
</div>
```

### Using multiple properties
![ParserPlugin Syntax](https://palexcom.github.io/PHPStructuredData/images/parser-plugin-syntax-v1.3.0.png)  
It is possible, using a combination of these, to specify multiple properties including some which are specific for a schema and others which are generic. The order of the building blocks isn't significant and a white space is used as a separator.

###### Example:
```html
<div data-sd="Article">
    <p data-sd="articleBody">This is my article</p>
    <p data-sd="Article.wordcount">4</p>
    <p data-sd="Recipe.recipeCategory Article.articleSection description">Amazing dessert recipes</p>
</div>
```

This will be output using ```Microdata``` semantics as:
```html
<div itemscope itemtype="http://schema.org/Article">
    <p itemprop="articleBody">This is my article</p>
    <p itemprop="wordcount">4</p>
    <p itemprop="articleSection">Amazing dessert recipes</p>
</div>
```
Or using ```RDFa``` semantics as:
```html
<div vocab="http://schema.org" typeof="Article">
    <p property="articleBody">This is my article</p>
    <p property="wordcount">4</p>
    <p property="articleSection">Amazing dessert recipes</p>
</div>
```

##### Nesting schemas
Sometimes it is necessary to nest schemas – for example if you want to describe a person when you have the Article schema open. This is possible using nested schemas. To use this, simply append the schema preceeded by a full stop, __after__ the property.  Once you have finished using the nested schema, close the containing tag, and re-set the original schema.

###### Example:
```html
<div data-sd="Article">
    <p data-sd="articleBody">This is my article</p>
    <p data-sd="Article.wordcount">4</p>
    <div data-sd="author.Person">
        <p data-sd="Person name">John Doe</p>
    </div>
    <p data-sd="Article keywords">Cake</p>
</div>
```

This will be output using ```Microdata``` semantics as:
```html
<div itemscope itemtype="http://schema.org/Article">
    <p itemprop="articleBody">This is my article</p>
    <p itemprop="wordcount">4</p>
    <div itemprop="author" itemscope itemtype="http://schema.org/Person">
        <p itemprop="name">John Doe</p>
    </div>
    <p itemprop="keywords">Cake</p>
</div>
```
Or using ```RDFa``` semantics as:
```html
<div vocab="http://schema.org" typeof="Article">
    <p property="articleBody">This is my article</p>
    <p property="wordcount">4</p>
    <div property="author" vocab="http://schema.org" typeof="Person">
        <p property="name">John Doe</p>
    </div>
    <p itemprop="keywords">Cake"</p>
</div>
```

##### The Algorithm:
1. First the parser checks for __setTypes__. If one or more matches are found then the current global scope will be updated with the first match. At this point if there are no specific or generic properties the algorithm will finish and replace the data tag with the specified scope. Otherwise continue to point 2.
2. The parser checks for __specific item properties__. If one or more valid matches are found, then the algorithm will finish and replace the data tag with the first match property. Otherwise go to point 3
3. The parser checks for __generic properties__. If one or more valid matches are found, then the algorithm will replace the data tag with the first property that is matched, and complete the algorithm.

Usage Example
-------------
Let's suppose that you already have an instance of the ```ParserPlugin``` library. And you need to add Microdata or RDFa semantics to the following HTML which is part of an article (_e.g._ ```$parser = new PHPStructuredData\ParserPlugin('microdata'); $scope='Article';```).
```html
<div data-sd="<?php echo $scope;?>">
    <!-- Title -->
    <span data-sd="Review.itemReviewed name">
        How to Tie a Reef Knot
    </span>
    <!-- Author -->
    <span>
        Written by
        <span data-sd="author.Person">
            <span data-sd="name">John Doe</span>
        </span>
    </span>
    <!-- Date published -->
    <meta data-sd='<?php echo $scope;?> datePublished' content='2014-01-01T00:00:00+00:00'/>1 January 2014
    <!-- Content -->
    <span data-sd='reviewBody articleBody'>
        Lorem ipsum dolor sit amet...
    </span>
<div>
```
The ```Microdata``` output will be:
```html
<div itemscope itemtype='https://schema.org/Article'>
    <!-- Title -->
    <span itemprop='name'>
        How to Tie a Reef Knot
    </span>
    <!-- Author -->
    <span>
        Written by
        <span itemprop='author' itemscope itemtype='https://schema.org/Person'>
            <span itemprop='name'>John Doe</span>
        </span>
    </span>
    <!-- Date published -->
    <meta itemprop='datePublished' content='2014-01-01T00:00:00+00:00'/>1 January 2014
    <!-- Content -->
    <span itemprop='articleBody'>
        Lorem ipsum dolor sit amet...
    </span>
<div>
```
The ```RDFa``` output will be:
```html
<div vocab='https://schema.org' typeof='Article'>
    <!-- Title -->
    <span property='name'>
        How to Tie a Reef Knot
    </span>
    <!-- Author -->
    <span>
        Written by
        <span property='author' vocab='https://schema.org' typeof='Person'>
            <span property='name'>John Doe</span>
        </span>
    </span>
    <!-- Date published -->
    <meta property='datePublished' content='2014-01-01T00:00:00+00:00'/>1 January 2014
    <!-- Content -->
    <span property='articleBody'>
        Lorem ipsum dolor sit amet...
    </span>
<div>
```
Instead, if you decide to change the current Type (_e.g._ ```$scope="Review";```).  
The ```Microdata``` output will be:
```html
<div itemscope itemtype='https://schema.org/Review'>
    <!-- Title -->
    <span itemprop='itemReviewed'>
        How to Tie a Reef Knot
    </span>
    <!-- Author -->
    <span>
        Written by
        <span itemprop='author' itemscope itemtype='https://schema.org/Person'>
            <span itemprop='name'>John Doe</span>
        </span>
    </span>
    <!-- Date published -->
    <meta itemprop='datePublished' content='2014-01-01T00:00:00+00:00'/>1 January 2014
    <!-- Content -->
    <span itemprop='reviewBody'>
        Lorem ipsum dolor sit amet...
    </span>
<div>
```
The ```RDFa``` output will be:
```html
<div vocab='https://schema.org' typeof='Review'>
    <!-- Title -->
    <span property='itemReviewed'>
        How to Tie a Reef Knot
    </span>
    <!-- Author -->
    <span>
        Written by
        <span property='author' vocab='https://schema.org' typeof='Person'>
            <span property='name'>John Doe</span>
        </span>
    </span>
    <!-- Date published -->
    <meta property='datePublished' content='2014-01-01T00:00:00+00:00'/>1 January 2014
    <!-- Content -->
    <span property='reviewBody'>
        Lorem ipsum dolor sit amet...
    </span>
<div>
```

Documentation
-------------
```PHPStructuredData``` libraries use the ```types.json``` file to check and output validated semantics, that file contains all the available Types and Properties from the http://schema.org vocabulary, and it was generated automatically with the https://github.com/PAlexcom/Spider4Schema web crawler.

Todos
-----
##### StructuredData  
* Add ```itemref``` support.
* Add multiple fallbacks support to ```StructuredData```.
* Add to the ```types.json``` all the required properties specified by Google, Yandex, Baidu.

License
-------
PHPStructuredData is licensed under the MIT License – see the LICENSE file for details.
