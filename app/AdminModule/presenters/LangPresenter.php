<?php

namespace App\AdminModule\Presenters;

use App\Forms\Lang\EditKeysControl;
use Caloriscz\Lang\LangListControl;
use Nette\Neon\Exception;
use Nette\Neon\Neon;

/**
 * Language presenter.
 */
class LangPresenter extends BasePresenter
{

    protected function createComponentEditKeys(): EditKeysControl
    {
        return new EditKeysControl($this->database);
    }

    protected function createComponentLangList(): LangListControl
    {
        return new LangListControl($this->database);
    }

    public function handleGenerate($id): void
    {
        $directory = '';
        $dictionary = $this->database->table('lang_list')->get($id);
        $key = $this->database->table('lang_keys')->where(['lang_list_id' => $id]);

        $neon_cs = '';

        foreach ($key as $item) {
            if ('' !== $item->directory || '' !== $item->path || '' !== $item->value_cs) {
                if ($item->directory !== $directory) {
                    $neon_cs .= $item->directory . ':' . '&#13;&#10';
                }

                $neon_cs .= '&#9;' . $item->path . ': "' . $item->value_cs . '"&#13;&#10;';
                $directory = $item->directory;
            }
        }

        $dictionary_cs = substr($neon_cs, 0, -10);

        $neon_en = '';

        foreach ($key as $item) {
            if ('' !== $item->directory || '' !== $item->path || '' !== $item->value_en) {
                if ($item->directory !== $directory) {
                    $neon_en .= $item->directory . ':' . '&#13;&#10';
                }

                $neon_en .= '&#9;' . $item->path . ': "' . $item->value_en . '"&#13;&#10;';
                $directory = $item->directory;
            }
        }

        $dictionary_en = substr($neon_en, 0, -10) . '</textarea>';

        echo $dictionary->title . '<br>';
        echo '<textarea style="width: 1200px; height: 600px">' . $dictionary_cs . '</textarea>';
        echo '<br>-----<br>';
        echo '<textarea style="width: 1200px; height: 600px">' . $dictionary_en . '</textarea>';


        die();
    }

    public function renderDefault(): void
    {
        $this->template->categoryId = null;
    }
}