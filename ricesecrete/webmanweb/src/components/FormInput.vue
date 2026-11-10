<template>
    <div class="relative pl-[10px] pt-[12px] pb-[35px] w-[100%] h-[60px] rounded-[10px] input-bg" @click="focusInput">
        <div class="flex items-center mb-[5px]">
            <div class="mr-[10px] text-[10px] text-[#AAAAAA] font-bold tracking-[1px]">{{ label }}</div>
            <div class="text-[9px] text-[#616161]">{{ placeholder }}</div>
        </div>
        <input ref="inputRef" :value="modelValue" @input="$emit('update:modelValue', $event.target.value)"
            :type="computedType" :name="name" :maxlength="props.maxlength" autocomplete="new-password" readonly
            @focus="removeReadonly"
            class="w-full h-full bg-transparent border-none outline-none text-[12px] text-white tracking-[1px] font-bold" />
        <div v-if="showToggle" class="absolute right-[15px] top-[23px]" @click.stop="toggleVisibility">
            <img :src="$getImageUrl(isVisible ? 'eye-open.png' : 'eye-close.png')" alt="" class="w-[15px] h-[15px]" />
        </div>
    </div>
</template>

<script setup>
import { ref, computed, nextTick } from 'vue'

const props = defineProps({
    modelValue: String,
    label: String,
    placeholder: String,
    type: {
        type: String,
        default: 'text'
    },
    name: String,
    showToggle: {
        type: Boolean,
        default: false
    },
    maxlength: {
        type: Number,
        default: 50
    }
})

const emit = defineEmits(['update:modelValue'])

const inputRef = ref()
const isVisible = ref(false)

const computedType = computed(() => {
    if (props.showToggle) {
        return isVisible.value ? 'text' : 'password'
    }
    return props.type
})

const focusInput = () => {
    inputRef.value?.focus()
}

const toggleVisibility = () => {
    isVisible.value = !isVisible.value
}

const removeReadonly = (event) => {
    event.target.removeAttribute('readonly')
}
</script>

<style scoped>
.input-bg {
    background: #37211B;
}
</style>