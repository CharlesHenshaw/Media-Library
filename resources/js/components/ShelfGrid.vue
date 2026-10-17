<template>
    <!-- No column gap, so each card's shelf plank joins its neighbours' into one continuous shelf per row. -->
    <div class="grid grid-cols-2 gap-y-10 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 2xl:grid-cols-7">
        <slot />
    </div>
</template>
