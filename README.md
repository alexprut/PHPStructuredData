PHPStructuredData [![Build Status](https://travis-ci.org/PAlexcom/PHPStructuredData.svg)](https://travis-ci.org/PAlexcom/PHPStructuredData)
============
A PHP library to implement and output http://schema.org microdata semantics.    
This library was merged in the Joomla master branch and now available from version 3.2 (called [JMicrodata](https://github.com/joomla/joomla-cms/tree/master/libraries/joomla/microdata "JMicrodata")).  
Created during the Google Summer of Code 2013 for my Joomla! project.  

Quick overview
--------------
The library was designed with this goals in mind:  
1. __Having the possibility to switch the Microdata Type dynamically__, you just change the Type (there are more than 550+ different available Types).  
2. Display  __validated semantics__, the library takes care of displaying data in the correct format (e.g. all the dates in the ISO standard).  
3. __Enable/disable the microdata__ semantics.  
4. __Fallbacks__, you should never lose any meaningful semantic (e.g. if you switch the page type and it doesn’t have an author property it will fallback to the Person type with the name property).  
  
Example
-------
Let's suppose that you allready use an istance of ```$microdata = new PHPStructuredData($type);``` the following code which is part of your article, and __you've selected__ ```$type="article"``` __the Article type__.
```php
<div <?php echo $microdata->displayScope();?>>
	<!-- Author of the content -->
    <span>
    	Written by <?php echo $microdata->content('Alexandru Pruteanu')->property('author')->fallback('Person', 'name')->display();?>
    </span>
    <!-- The content -->
    <?php echo $microdata->content('Here is the article text...')->property('articleBody')->display();?>
<div>
```
It will render:
```html
<div itemscope itemtype='https://schema.org/Article'>
    <!-- Author of the content -->
    <span>
    	Written by
        <span itemprop='author' itemscope itemtype='https://schema.org/Person'>
            <span itemprop='name'>Alexandru Pruteanu</span>
        </span>
    </span>
    <!-- The content -->
    <span itemprop='articleBody'>Here is the article text...</span>
<div>
```
Instead, if you decide to change the current Type, let's say __you change in__ ```$type="thing"```  __the Thing type__
It will render:
```html
<div itemscope itemtype='https://schema.org/Thing'>
    <!-- Author of the content -->
    <span>
    	Written by
        <span itemscope itemtype='https://schema.org/Person'>
            <span itemprop='name'>Alexandru Pruteanu</span>
        </span>
    </span>
    <!-- The content -->
    Here is the article text...
<div>
```
As you can see ```Alexandru Pruteanu``` __fallbacks__ to the Person type, and there is no loss of information, even if the current Type doesn't have an ```author``` Property it will display important semantic information for the machines, the search engines know that there is a Person ```Alexandru Pruteanu```. And everything is valid, done fast and automatically.
Instead, if you don't need all that microdata information, __you just disable that feature__ ```$microdata->enable(false)```.
It will render:
```html
<div>
    <!-- Author of the content -->
    <span>Written by Alexandru Pruteanu</span>
    <!-- The content -->
    Here is the article text...
<div>
```
Once again everything is done by the library. You don't need 558 different overrides, you just play with the global params.

Documentation
-------------
```PHPStructuredData``` library uses the ```types.json``` file to check and output validated Microdata semantics, that file was automatically created with the https://github.com/PAlexcom/Spider4Schema web crawler.   
      
For further documentation on ```PHPStructuredData``` see http://docs.joomla.org/microdata    
  

Usage
-----
First of all you need to make an instance of the library:  
```php
<?php $microdata = new PHPMicrodata('Article'); ?>
```
So let's suppose that you have the following _string_ which is part of your article and the current scope is _Article_:   
```php
<?php echo 'Written by Alexandru Pruteanu'; ?>
```  
And the microdata you need to add is an _author_ property:   
```php
<?php echo 'Written by ' . $microdata->content('Alexandru Pruteanu')->property('author')->fallback('Person', 'name')->display(); ?>
```  
The library will display:  
```html
Written by  
<span itemprop='author' itemscope itemtype='https://schema.org/Person'>
	<span itemprop='name'>
		Alexandru Pruteanu
	</span>
</span>
```
— What happens if the current scope is something else than _Article_, for example a _Product_ scope, and the current scope doesn't have an author property?  
Well it will fallback in:  
```html
Written by
<span itemscope itemtype='https://schema.org/Person'>
	<span itemprop='name'>
		Alexandru Pruteanu
	</span>
</span>
```
— If I want to disable the microdata semantics output?  
You can simply disable the microdata output by calling the following function:  
```php
<?php $microdata->enable(false); ?>
```  
The library will display the following:   
```html
Written by Alexandru Pruteanu
```  

License
-------
PHPStructuredData is licensed under the MIT License - see the LICENSE file for details.