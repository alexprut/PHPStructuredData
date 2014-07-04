PHPStructuredData [![Build Status](https://travis-ci.org/PAlexcom/PHPStructuredData.svg)](https://travis-ci.org/PAlexcom/PHPStructuredData)
============
A set of PHP libraries that use the http://schema.org vocabulary to implement and output Microdata or RDFa Live 1.1 semantics.  
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
![Class Diagram](https://palexcom.github.io/PHPStructuredData/images/classdiagram.png)

Example
-------
Let's suppose that you already have an instance of the Microdata or RDFa library. And you need to add Microdata or RDFa semantics to the following HTML which is part of an article (_e.g._ ```$sd = new PHPMicrodata('Article');```).
```php
<div <?php echo $sd->displayScope();?>>
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
The ```PHPMicrodata``` library will render:
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
The ```PHPRDFa``` library will render:
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
Instead, if you decide to change the current Type (_e.g._ ```$sd->setType('Thing');```).  
The ```PHPMicrodata``` library will render:
```html
<div itemscope itemtype='https://schema.org/Thing'>
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
    1 January 2014
    <!-- Content -->
    Lorem ipsum dolor sit amet...
<div>
```
The ```PHPRDFa``` library will render:
```html
<div vocab='https://schema.org' typeof='Thing'>
    <!-- Title -->
    <span itemprop='name'>
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
    1 January 2014
    <!-- Content -->
    Lorem ipsum dolor sit amet...
<div>
```
As you can see ```John Doe``` __fallbacks__ to the _Person_ Type, and there is no loss of information, even if the current Type doesn't have an _author_ Property it will display important information for the machines, search engines know that there is a Person ```John Doe```.  
Instead, if you decide to not render Microdata or RDFa semantics, you just __disable the library__ output (_e.g._ ```$sd->enable('false');```).  
Both ```PHPMicrodata``` and ```PHPRDFa``` library will render:
```html
<div >
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

Documentation
-------------
```PHPStructuredData``` libraries use the ```types.json``` file to check and output validated semantics, that file contains all the available Types and Properties from the http://schema.org vocabulary, and it was generated automatically with the https://github.com/PAlexcom/Spider4Schema web crawler.

License
-------
PHPStructuredData is licensed under the MIT License – see the LICENSE file for details.