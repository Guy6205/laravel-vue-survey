<template>
    <fieldset class="mb-4">
        <div>
            <legend class="text-base font-medium text-gray-900">
                {{ index + 1 }}. {{ question.question }}
            </legend>
        </div>
        <p class="text-gray-500 text-sm">
            {{ question.description }}
        </p>
        <div class="mt-3">

            <!-- Question Types -->

            <!-- Select -->
            <div v-if="question.type === 'select'">
                <select 
                    :value="modelValue"
                    @change="emits('update:ModelValue', $event.target.value)"
                    class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                >   
                    <option value="">Please Select</option>
                    <option v-for="option in question.data.options" :key="option.uuid" :value="option.value">
                        {{ option.value }}
                    </option>
                </select>
            </div>

            <!-- Radio -->
            <div v-else-if="question.type === 'radio'">
                <div 
                    v-for="(option, index) of question.data.options"
                    :key="option.uuid"
                    class="flex items-center mb-1"
                >
                    <input 
                        :id="option.uuid"
                        :name="'question' + question.id"
                        :value="option.value"
                        @change="emits('update:ModelValue', $event.target.value)"
                        type="radio"
                        class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                    />
                    <label
                        :for="option.uuid"
                        class="ml-3 block text-sm font-medium text-gray-700"
                    >
                        {{ option.value }}
                    </label>
                </div>
            </div>

            <!-- Checkbox -->
            <div v-else-if="question.type === 'checkbox'">
                <div 
                    v-for="(option, index) of question.data.options"
                    :key="option.uuid"
                    class="flex items-center mb-1"
                >
                <input 
                    :id="option.uuid"
                    v-model="model[option.value]"
                    @change="onCheckBoxChange"
                    type="checkbox"
                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                />
                <label
                    :for="option.uuid"
                    class="ml-3 block text-sm font-medium text-gray-700"
                >
                    {{ option.value }}
                </label>
                </div>
            </div>

            <!-- Text -->
            <div v-else-if="question.type === 'text'">
                <input 
                    type="text"
                    :value="modelValue"
                    @input="emits('update:modelValue', $event.target.value)"
                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                />

            </div>

            <!-- Textarea -->
            <div v-else-if="question.type === 'textarea'">
                <input 
                    :value="modelValue"
                    @input="emits('update:modelValue', $event.target.value)"
                    class="mt-1 focus:ring-indigo-500 focus:border-indigo-500 block w-full shadow-sm sm:text-sm border-gray-300 rounded-md"
                />
            </div>
        </div>
        <hr class="mb-4" />
    </fieldset>
</template>

<script setup>
import { ref } from 'vue';

const { question, index, modelValue } = defineProps({
    question: Object,
    index: Number,
    modelValue: [String, Array],
});

const emits = defineEmits(['update:modelValue']);

let model;
if (question.type === 'checkbox') {
    model = ref({});
}

function shouldHaveOptions() {
  return ["select", "radio", "checkbox"].includes(question.type);
}

function onCheckBoxChange($event) {
    const selectedOptions = [];

    for (let uuid in model.value) {
        if (model.value[uuid]) {
        selectedOptions.push(uuid);
        }
    }

    emits('update:modelValue', selectedOptions);
}

</script>

<style></style>