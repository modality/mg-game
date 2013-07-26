<?php


/**
 *
 * @package    MG
 * @author Nikolay Kondikov <nikolay.kondikov@sirma.bg>
 */
class PyramidGame extends NexTagGame
{
    private static $TIME_TO_PLAY = 120;
    private static $LETTERS_STEP = 2;

    public function parseTags(&$game, &$game_model)
    {
        $data = array();
        $imageId = 0;
        $currentTag = "";
        // loop through all submissions for this turn and set ONLY THE FIRST TAG
        foreach ($game->request->submissions as $submission) {
            $imageId = $submission["image_id"];
            $imageTags = array();
            // Attempt to extract these
            foreach (MGTags::parseTags($submission["tags"]) as $tag) {
                $imageTags[strtolower($tag)] = array(
                    'tag' => $tag,
                    'weight' => 1,
                    'type' => 'new',
                    'tag_id' => 0
                );
                $currentTag = $tag;
                break;
            }
            // add the extracted tags to the image info
            $data[$imageId] = $imageTags;
            break;
        }

        $level = $this->getLevel();
        if (is_null($level)) {
            $level = new PyramidDTO();
            $level->level = 1;
            $level->isAccepted = false;
        } else if ($level->isAccepted) {
            //Move to next level
            $level->level++;
            $level->levelTurn = 0;
            $level->isAccepted = false;
            $level->countTags = 0;
            $level->tag = "";
        }
        if ($imageId > 0) {
            $found = false;
            $imageTags = $this->getImageTags($level, $imageId);
            foreach ($imageTags as $val) {
                if ($currentTag == strtolower($val['tag'])) {
                    $data[$imageId][$currentTag]['type'] = 'match';
                    $data[$imageId][$currentTag]['tag_id'] = $val['tag_id'];
                    $found = true;
                    break;
                }
            }

            if ($level->countTags == 0) {
                $level->countTags = count($imageTags);
            }

            //the answer is incorrect. Player can submit another word
            $level->levelTurn++;
            $level->isAccepted = false;
            $level->tag = $currentTag;

            if ($found && ($level->level + PyramidGame::$LETTERS_STEP) == strlen($currentTag)) {
                //the answer is marked as correct and the player moves on to the next length tag
                $level->isAccepted = true;
            } else if (($level->level + PyramidGame::$LETTERS_STEP) == strlen($currentTag)) {
                //run the “freebie” algorithm to determine whether or not we lie to the players
                $chance = pow($level->levelTurn, 2) / (10 * ($level->countTags + 1));
                if ($chance > 0.5) $chance = 0.5;
                $rand = mt_rand() / mt_getrandmax();
                if ($rand < $chance) {
                    $level->isAccepted = true;
                }
            }
            $this->saveLevel($level);
        }

        return $data;
    }

    /**
     *
     * @param object $game The game object
     * @param object $game_model The game model
     * @param Array the tags submitted by the player for each image
     * @return Array the turn information that will be sent to the players client
     */
    public function getTurn(&$game, &$game_model, $tags = array())
    {
        $data = array();

        $startTime = $this->getStartTime();
        $now = time();

        // check if the game is not actually over
        if (($now - $startTime) < PyramidGame::$TIME_TO_PLAY) {

            $image = $this->getImage();
            if (empty($image)) {
                $imageSets = $this->getImageSets($game, $game_model);
                $data["images"] = array();
                $images = $this->getImages($imageSets, $game, $game_model);
                if ($images && count($images) > 0) {
                    $i = array_rand($images, 1); // select one random item out of the images
                    $image = $images[$i];
                    $this->setImage($image);
                } else
                    throw new CHttpException(600, $game->name . Yii::t('app', ': Not enough images available'));
            }

            $lastLevel = $this->getLevel();
            if (is_null($lastLevel)) {
                $lastLevel = new PyramidDTO();
                $lastLevel->level = 1;
                $lastLevel->levelTurn = 1;
                $lastLevel->isAccepted = false;
            }

            $path = Yii::app()->getBaseUrl(true) . Yii::app()->fbvStorage->get('settings.app_upload_url');
            $data["images"][] = array(
                "image_id" => $image["id"],
                "full_size" => $path . "/images/" . $image["name"],
                "thumbnail" => $path . "/thumbs/" . $image["name"],
                "final_screen" => $path . "/scaled/" . MGHelper::createScaledImage($image["name"], "", "scaled", 212, 171, 80, 10),
                "scaled" => $path . "/scaled/" . MGHelper::createScaledImage($image["name"], "", "scaled", $game->image_width, $game->image_height, 80, 10),
                "licences" => $image["licences"],
                "level" => $lastLevel->level,
                "tag_accepted" => $lastLevel->isAccepted
            );

            // extract needed licence info
            $data["licences"] = $this->getLicenceInfo($image["licences"]);

            // prepare further data
            $data["tags"] = array();
            // in the first turn this field is empty in further turns it contains the
            // previous turns weightened tags
            $data["tags"]["user"] = $tags;

            // the following lines call the wordsToAvoid methods of the activated dictionary
            // plugin this generates a words to avoid list
            $used_images = array();
            array_push($used_images, $image['id']);
            $data["wordstoavoid"] = array();
            $plugins = PluginsModule::getActiveGamePlugins($game->game_id, "dictionary");
            if (count($plugins) > 0) {
                foreach ($plugins as $plugin) {
                    if (method_exists($plugin->component, "wordsToAvoid")) {
                        // this method gets all elements by reference. $data["wordstoavoid"] might be changed
                        $plugin->component->wordsToAvoid($data["wordstoavoid"], $used_images, $game, $game_model, $tags);
                    }
                }
            }
        } else {
            // the game is over thus the needed info is sparse
            $data["tags"] = array();
            $data["tags"]["user"] = $tags;
            $data["licences"] = array(); // no need to show licences on the last screen as the previous turns are cached by javascript and therefore all licence info is available
            $this->reset();
        }
        return $data;
    }

    /**
     * This method return start time of the game
     *
     * @return int
     */
    protected function getStartTime()
    {
        $time = time();
        $api_id = Yii::app()->fbvStorage->get("api_id", "MG_API");
        if (!isset(Yii::app()->session[$api_id . '_PYRAMID_START_TIME'])) {
            Yii::app()->session[$api_id . '_PYRAMID_START_TIME'] = $time;
        } else {
            $time = Yii::app()->session[$api_id . '_PYRAMID_START_TIME'];
        }
        return $time;
    }

    /**
     * Retrieve the IDs of all images that have been seen/used by the current user
     * on a per game and per session basis.
     *
     * @return ArrayObject of the current image
     */
    protected function getImage()
    {
        $image = array();
        $api_id = Yii::app()->fbvStorage->get("api_id", "MG_API");
        if (isset(Yii::app()->session[$api_id . '_PYRAMID_IMAGE'])) {
            $image = Yii::app()->session[$api_id . '_PYRAMID_IMAGE'];
        }
        return $image;
    }

    /**
     * Add image stored in the current session for the currently
     * played game
     *
     * @param ArrayObject $image the image that have been shown to the user
     */
    protected function setImage($image)
    {
        $api_id = Yii::app()->fbvStorage->get("api_id", "MG_API");
        Image::model()->setLastAccess(array($image['id']));
        Yii::app()->session[$api_id . '_PYRAMID_IMAGE'] = $image;
    }

    public static function reset()
    {
        $api_id = Yii::app()->fbvStorage->get("api_id", "MG_API");
        unset(Yii::app()->session[$api_id . '_PYRAMID_IMAGE']);
        unset(Yii::app()->session[$api_id . '_PYRAMID_LEVELS']);
        unset(Yii::app()->session[$api_id . '_PYRAMID_START_TIME']);
        unset(Yii::app()->session[$api_id . '_PYRAMID_IMAGE_TAGS']);
    }

    /**
     * Get last played level of pyramid game
     *
     * @return PyramidDTO|null
     */
    private function getLevel()
    {
        $level = null;
        $api_id = Yii::app()->fbvStorage->get("api_id", "MG_API");
        if (isset(Yii::app()->session[$api_id . '_PYRAMID_LEVELS'])) {
            $levels = Yii::app()->session[$api_id . '_PYRAMID_LEVELS'];
            $level = unserialize(end($levels));
        }
        return $level;
    }

    /**
     * Save current level which will be played of pyramid game
     *
     * @param PyramidDTO $level
     */
    private function saveLevel(PyramidDTO $level)
    {
        $levels = array();
        $api_id = Yii::app()->fbvStorage->get("api_id", "MG_API");
        if (isset(Yii::app()->session[$api_id . '_PYRAMID_LEVELS'])) {
            $levels = Yii::app()->session[$api_id . '_PYRAMID_LEVELS'];
        }
        array_push($levels, serialize($level));
        Yii::app()->session[$api_id . '_PYRAMID_LEVELS'] = $levels;
    }

    /**
     * @param PyramidDTO $level
     * @param int $imageId
     * @return array
     */
    private function getImageTags(PyramidDTO $level, $imageId)
    {
        $api_id = Yii::app()->fbvStorage->get("api_id", "MG_API");
        $imageTags = Yii::app()->session[$api_id . '_PYRAMID_IMAGE_TAGS'];

        if (!is_null($imageTags) && ($level->level + PyramidGame::$LETTERS_STEP) == strlen($imageTags[0]["tag"])) {
            return $imageTags;
        } else {
            $imageTags = MGTags::getTagsByLength($imageId, ($level->level + PyramidGame::$LETTERS_STEP));
            if(empty($imageTags)){
                $tag = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0,$level->level + PyramidGame::$LETTERS_STEP);
                $imageTags[0]["tag"] = $tag;
                $imageTags[0]["tag_id"] = -1;
            }
            Yii::app()->session[$api_id . '_PYRAMID_IMAGE_TAGS'] = $imageTags;
            return $imageTags;
        }
    }
}

class PyramidDTO
{
    /**
     * @var int
     */
    public $level = 0;
    /**
     * @var int
     */
    public $levelTurn = 0;
    /**
     * @var int
     */
    public $countTags = 0;
    /**
     * @var string
     */
    public $tag = "";
    /**
     * @var bool
     */
    public $isAccepted = false;
}

?>
