<?php namespace Fw\Editable\Components;

use File;
use BackendAuth;
use Cms\Classes\Content;
use Cms\Classes\ComponentBase;

class NotEditable extends ComponentBase
{
    public $content;
    public $isEditor;
    public $file;
    public $fileMode;

    public function componentDetails()
    {
        return [
            'name' => 'fw.editable::lang.component_noteditable.name',
            'description' => 'fw.editable::lang.component_noteditable.description',
        ];
    }

    public function defineProperties()
    {
        return [
            'file' => [
                'title' => 'fw.editable::lang.component_editable.property_file.title',
                'description' => 'fw.editable::lang.component_editable.property_file.description',
                'default' => '',
                'type' => 'dropdown',
            ]
        ];
    }

    public function getFileOptions()
    {
        return Content::sortBy('baseFileName')->lists('baseFileName', 'fileName');
    }

    public function onRun()
    {
        $this->isEditor = $this->checkEditor();
    }

    private function createContent($file, $content = NULL) {
        $object = Content::inTheme($this->page->controller->getTheme());
        if (empty($content)) $content = pathinfo($file, PATHINFO_FILENAME);
        $objectData = [
            'fileName' => $file,
            'markup' => $content
        ];
        $object->fill($objectData);
        $object->save();

        $this->fireEvent('object.save', [$object, 'content']);

        return $content;
    }

    public function onRender()
    {
        $this->file = $this->property('file');
        $this->fileMode = File::extension($this->property('file'));
        $justCreated = false;

        if ((Content::loadCached($this->page->controller->getTheme(), $this->file)) === null) {
            if (!$this->isEditor)
                return '';

            $content = $this->createContent($this->file);
            $justCreated = true;
        }

        if (!$justCreated) $content = $this->renderContent($this->file);

        if (class_exists('\RainLab\Translate\Classes\Translator')){
            $locale = \RainLab\Translate\Classes\Translator::instance()->getLocale();
            $fileName = substr_replace($this->file, '.'.$locale, strrpos($this->file, '.'), 0);
            if ((Content::loadCached($this->page->controller->getTheme(), $fileName)) !== null) {
                $this->file = $fileName;
                $content = $this->renderContent($this->file);
            } else {
                $defaultLocale = \RainLab\Translate\Classes\Translator::instance()->getDefaultLocale();

                if ($locale != $defaultLocale) {
                    //don't create the localized file yet, but change the file property so if the user changes it it's saved to the proper localized file
                    $this->file = $fileName;
                }
            }
        }

        //replace paragraphs with break lines
        $content = str_replace(array('<p>','</p>'),array('','<br />'), $content);
        //remove all html tags except break lines
        $content = strip_tags($content, '<br>');
        //remove EOL
        $content = preg_replace( "/\r|\n/", "", $content);
        //remove excess <br> or <br /> from the end of the text
        $content = preg_replace('#(( ){0,}<br( {0,})(/{0,1})>){1,}$#i', '', $content);

        return $content;

    }

    public function checkEditor()
    {
        $backendUser = BackendAuth::getUser();
        return $backendUser && ($backendUser->hasAccess('cms.manage_content') || $backendUser->hasAccess('rainlab.pages.manage_content'));
    }

}