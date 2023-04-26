<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSurveyRequest;
use App\Http\Requests\UpdateSurveyRequest;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Http\Resources\SurveyResource;;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;

class SurveyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        return SurveyResource::collection(Survey::where('user_id', $user->id)->paginate(5));
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @param \App\Http\Requests\StoreSurveyRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreSurveyRequest $request)
    {
        $data = $request->validated();

        // check if image provided
        // if so save on local filesystem
        if (isset($data['image'])) {
            $relativePath = $this->saveImage($data['image']);
            $data['image'] = $relativePath;
        }

        $survey = Survey::create($data);

        // handle survey questions
        foreach ($data['questions'] as $question) {
            $question['survey_id'] = $survey->id;
            $this->createQuestion($question);
        }

        return new SurveyResource($survey);
    }

    /**
     * Display the specified resource.
     * 
     * @param \App\Models\Survey $survey
     * @return \Illuminate\Http\Response
     */
    public function show(Survey $survey, Request $request)
    {
        $user = $request->user();

        if ($user->id !== $survey->user_id) {
            return abort(403, 'Unauthorized action.');
        }
        return new SurveyResource($survey);
    }

    /**
     * Update the specified resource in storage.
     * 
     * @param \App\Http\Requests\UpdateSurveyRequest $request
     * @param \App\Models\Survey $survey
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateSurveyRequest $request, Survey $survey)
    {
        $data = $request->validated();

        // if image is supplied, save on local filesystem
        if (isset($data['image'])) {
            $relativePath = $this->saveImage($data['image']);
            $data['image'] = $relativePath;

            // delete old image
            if ($survey->image) {
                $absolutePath = public_path($survey->image);
                File::delete($absolutePath);
            }
        }

        $survey->update($data);  
        
        // handle survey questions

        // get existing question ids
        $existingIds = $survey->questions->pluck('id')->toArray();

        // get new question ids
        $newIds = Arr::pluck($data['questions'], 'id');

        // get ids of questions to be deleted
        $toDelete = array_diff($existingIds, $newIds);

        // get ids of questions to be added
        $toAdd = array_diff($newIds, $existingIds);

        // delete questions
        SurveyQuestion::destroy($toDelete);

        // create new questions
        foreach ($data['questions'] as $question) {
            if (in_array($question['id'], $toAdd)) {
                // assign survey id to question
                $question['survey_id'] = $survey->id;
                // create question
                $this->createQuestion($question);
            }
        }

        // update existing questions
        $questionMap = collect($data['questions'])->keyBy('id');
        foreach ($survey->questions as $question) {
            if (isset($questionMap[$question->id])) {
                $this->updateQuestion($question, $questionMap[$question->id]);
            }
        }


        return new SurveyResource($survey);
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param \App\Models\Survey $survey
     * @return \Illuminate\Http\Response
     */
    public function destroy(Survey $survey, Request $request)
    {
        $user = $request->user();

        if ($user->id !== $survey->user_id) {
            return abort(403, 'Unauthorized action.');
        }
        
        $survey->delete();

        // delete old image
        if ($survey->image) {
            $absolutePath = public_path($survey->image);
            File::delete($absolutePath);
        }

        return response('', 204);
    }

    private function saveImage($image)
    {
        // check if image is a valid base64 string
        if (preg_match('/data:image\/(\w+);base64,/', $image, $type)) {

            // take out the base64 encoded text without mime type
            $image = substr($image, strpos($image, ',') + 1);

            //get the file extension
            $type = strtolower($type[1]);

            //check if type is an image
            if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                throw new Exception('Invalid image type');
            }
            
            // prepare and decode the image string
            $image = str_replace(' ', '+', $image);
            $image = base64_decode($image);

            // check if image is valid
            if ($image === false) {
                throw new Exception('Base64 decode failed');
            }

        } else {
            throw new \Exception('Invalid image data URI');
        }

        $dir = 'images/';
        $file = Str::random() . '.' . $type;
        $absolutePath = public_path($dir);
        $relativePath = $dir . $file;

        if (!File::exists($absolutePath)) {
            File::makeDirectory($absolutePath, 0755, true);
        }

        file_put_contents($relativePath, $image);

        return $relativePath;
    }

    private function createQuestion($data) 
    {
        if (is_array($data['data'])) {
            $data['data'] = json_encode($data['data']);
        }

        $validator = Validator::make($data, [
            'question' => 'required|string',
            'type' => ['required', Rule::in([
                Survey::TYPE_TEXT,
                Survey::TYPE_TEXTAREA,
                Survey::TYPE_SELECT,
                Survey::TYPE_RADIO,
                Survey::TYPE_CHECKBOX,
            ])],
            'description' => 'nullable|string',
            'data' => 'present',
            'survey_id' => 'exists:App\Models\Survey,id',
        ]);

        return SurveyQuestion::create($validator->validated());
    }

    private function updateQuestion(SurveyQuestion $question, $data)
    {
        if (is_array($data['data'])) {
            $data['data'] = json_encode($data['data']);
        }

        $validator = Validator::make($data, [
            'id' => 'exists:App\models\SurveyQuestion,id',
            'question' => 'required|string',
            'type' => ['required', Rule::in([
                Survey::TYPE_TEXT,
                Survey::TYPE_TEXTAREA,
                Survey::TYPE_SELECT,
                Survey::TYPE_RADIO,
                Survey::TYPE_CHECKBOX,
            ])],
            'description' => 'nullable|string',
            'data' => 'present',
        ]);

        return $question->update($validator->validated());
    }
}
