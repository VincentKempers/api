<template>
    <div class="interface-color-picker">
        <form action="">
            <label>
                <input type="radio"> 
                    color name
            </label>
            <label for="color-picker">
                <input type="radio"> color name
            </label>
            <label for="color-picker">
                <input type="radio"> color name
            </label>
            <label for="color-picker">
                <input type="radio"> color name
            </label>
        </form>
    </div>
</template>

<script>
export default {
  computed: {
    log() {}
  }
};
</script>

<style type="scss" scoped>
.color-400 {
  width: 2em;
  height: 2em;
  background-color: green;
}
.color-400:hover {
  width: 3em;
  height: 3em;
  transition: all 0.3s ease-in;
}
</style>
