<?php
/**
 * Copyright 2014 Bogdan Ghervan
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace forms\AddJob;

/**
 * Initializes the right add job form (simple or advanced) based on passed form data.
 *
 * @author      Bogdan Ghervan <bogdan.ghervan@gmail.com>
 * @copyright   2014 Bogdan Ghervan
 * @link        http://github.com/cronkeep/cronkeep
 * @license     http://opensource.org/licenses/Apache-2.0 Apache License 2.0
 */
class FormFactory
{
    const SIMPLE = 'simple';
    const ADVANCED = 'advanced';
    
    /**
     * Initializes the right add job form (simple or advanced) based on passed form data,
     * injects the data into the form and returns the form object.
     * 
     * @param array $formData
     * @return \forms\AddJob\AdvancedForm
     * @throws \RuntimeException
     */
    public static function createForm($formData)
    {
        if (empty($formData['mode'])) {
            throw new \RuntimeException('Mode is missing');
        }
        
        if ($formData['mode'] == self::SIMPLE) {
            $form = new SimpleForm();
        } else {
            $form = new AdvancedForm();
        }
        
        $form->setData($formData);
        return $form;
    }
}
