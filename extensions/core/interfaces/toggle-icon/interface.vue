<template>
	<div v-if="!checkbox">
		<input
		type="checkbox"
		:id="icon-toggle"
		@click="$emit('input', !value)">
		<label :for="icon-toggle" :style="{ color: `var(--${options.colorA})` }">
			<i class="material-icons">{{ icon }}</i>
		</label>
	</div>
</template>

<script>
import mixin from "../../../mixins/interface";

export default {
  mixins: [mixin],
  computed: {
    icon() {
      console.log(this.options.iconInactive);
      return this.value ? this.options.iconActive : this.options.iconInactive;
    }
  }
};
</script>

<style type="scss" scoped>
div {
  position: relative;
}
input {
  position: absolute;
  visibility: collapse;
}
</style>