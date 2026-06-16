<script type="text/x-template" id="cx-vui-button">
    <component
        :is="tagName"
        :class="classesList"
        :id="elementId"
        :disabled="disabled"
        v-bind="tagAtts"
        @click="handleClick"
        v-if="isVisible()"
    >
	<span :class="[ this.baseClass + '__content' ]">
		<slot name="label"></slot>
	</span>
        <span v-if="loading" :class="[ this.baseClass + '__loader' ]">
		<slot name="loadingIcon">
			<svg width="16" class="loader-icon" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M15.9023 9.06541L14.4611 9.55376C14.3145 10.2375 14.0214 10.8967 13.6305 11.5316L14.2901 12.899C14.3145 12.9478 14.2901 12.9966 14.2656 13.0455L12.971 14.3396C12.9221 14.364 12.8733 14.3884 12.8244 14.364L11.4565 13.7047C10.8458 14.0954 10.1863 14.364 9.47786 14.5105L8.98931 15.9512C8.98931 15.9756 8.96489 16 8.8916 16H7.08397C7.03511 16 6.98626 15.9756 6.96183 15.9267L6.47328 14.4861C5.78931 14.3396 5.12977 14.0466 4.49466 13.6559L3.12672 14.3152C3.07786 14.3396 3.02901 14.3152 2.98015 14.2908L1.6855 12.9966C1.66107 12.9478 1.63664 12.899 1.66107 12.8501L2.32061 11.4828C1.92977 10.8723 1.66107 10.213 1.5145 9.50493L0.0732824 9.01658C0.0244275 8.99216 0 8.96774 0 8.89449L0 7.08759C0 7.03875 0.0244275 6.98992 0.0732824 6.9655L1.5145 6.47715C1.66107 5.79346 1.9542 5.13418 2.34504 4.49933L1.6855 3.13194C1.66107 3.08311 1.6855 3.03427 1.70992 2.98544L3.00458 1.69131C3.05344 1.66689 3.10229 1.64247 3.15114 1.66689L4.51908 2.32616C5.12977 1.93548 5.78931 1.66689 6.49771 1.52038L6.98626 0.0797482C7.01069 0.0309124 7.03511 0.00649452 7.1084 0.00649452L8.91603 0.00649452C8.96489 -0.0179234 9.01374 0.0309124 9.03817 0.0797482L9.52672 1.52038C10.2107 1.66689 10.8702 1.9599 11.5053 2.35058L12.8733 1.69131C12.9221 1.66689 12.971 1.69131 13.0198 1.71572L14.3145 3.00986C14.3389 3.05869 14.3634 3.10753 14.3389 3.15636L13.6794 4.52374C14.0702 5.13418 14.3389 5.79346 14.4855 6.50157L15.9267 6.98992C15.9756 7.01434 16 7.03875 16 7.11201V8.91891C15.9756 8.99216 15.9511 9.04099 15.9023 9.06541ZM11.5786 6.9655C10.9924 4.98768 8.91603 3.86447 6.96183 4.45049C4.98321 5.03651 3.85954 7.11201 4.4458 9.06541C5.03206 11.0432 7.1084 12.1664 9.0626 11.5804C11.0412 11.0188 12.1649 8.94332 11.5786 6.9655Z"/></svg>
		</slot>
	</span>
    </component>
</script>
<script type="text/x-template" id="cx-vui-checkbox">
    <cx-vui-component-wrapper
        :elementId="currentId"
        :label="label"
        :description="description"
        :wrapper-css="wrapperCss"
        :preventWrap="preventWrap"
        v-if="isVisible()"
    >
        <div
            :class="{
			'cx-vui-checkgroup': true,
			'cx-vui-checkgroup--single-item': this.optionsList.length === 1
		}"
            :id="currentId"
            tabindex="0"
            @focus="handleParentFocus( $event )"
        >
            <div
                class="cx-vui-checkbox-wrap"
                v-for="( option, index ) in optionsList"
            >
                <div
                    :key="name + option.value + index"
                    :class="{
					'cx-vui-checkbox': true,
					'cx-vui-checkbox--disabled': disabled,
					'cx-vui-checkbox--checked': isChecked( option.value ),
					'cx-vui-checkbox--focused': isOptionInFocus( option.value ),
				}"
                    @click="handleInput( $event, option.value )"
                    @focus="handleFocus( $event, option.value )"
                    @blur="handleBlur( $event, option.value )"
                    @keyup.enter="handleInput( $event, option.value )"
                    tabindex="0"
                >
                    <input
                        :class="{
						'cx-vui-checkbox__input': true,
					}"
                        :type="inputType"
                        :name="name + '[' + option.value + ']'"
                        :value="inputValue( option.value )"
                        :checked="isChecked( option.value )"
                        :disabled="disabled"
                    >
                    <div
                        :class="{
						'cx-vui-checkbox__check': true,
						'cx-vui-checkbox__check--disabled': disabled,
						'cx-vui-checkbox__check--checked': isChecked( option.value ),
						'cx-vui-checkbox__check--focused': isOptionInFocus( option.value ),
					}"
                    >
                        <svg width="12" height="10" viewBox="0 0 12 10" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4.19531 5.05469L1.19531 3.25L0 4.44531L4.19531 9.25L12 1.44531L10.8047 0.25L4.19531 5.05469Z"/></svg>
                    </div>
                    <div
                        :class="{
						'cx-vui-checkbox__label': true,
					}"
                        v-html="option.label"
                    ></div>
                </div>
            </div>
        </div>
    </cx-vui-component-wrapper>
</script>
<script type="text/x-template" id="cx-vui-collapse">
    <div
        :class="{ 'cx-vui-collapse': true, 'cx-vui-collapse--collapsed': 'collapsed' === this.state }"
        v-if="isVisible()"
    >
        <div
            class="cx-vui-collapse__heading"
            @click="switchState"
        >
            <span :class="[ 'cx-vui-collapse__heading-icon', 'dashicons', this.iconArrow ]"></span>
            <slot name="title"></slot>
        </div>
        <div class="cx-vui-collapse__content">
            <slot name="content"></slot>
        </div>
    </div>
</script>
<script type="text/x-template" id="cx-vui-colorpicker">
    <cx-vui-component-wrapper
        :elementId="currentId"
        :label="label"
        :description="description"
        :wrapper-css="wrapperCss"
        :preventWrap="preventWrap"
        v-if="isVisible()"
    >
        <div
            :class="controlClasses"
            v-click-outside.capture="onClickOutside"
            v-click-outside:mousedown.capture="onClickOutside"
            v-click-outside:touchstart.capture="onClickOutside"
        >
            <div
                class="cx-vui-colorpicker__preview"
                @click="panelActive=!panelActive"
            >
			<span
                class="cx-vui-colorpicker__color"
            >
				<span v-if="isCloseVisible" :style="{ backgroundColor: currentValue }"></span>
			</span>
                <span
                    class="cx-vui-colorpicker__clear"
                    @click="clearColor"
                    v-if="isCloseVisible"
                >
				<svg width="10" height="10" viewBox="0 0 10 10" fill="#7B7E81" xmlns="http://www.w3.org/2000/svg">
					<path d="M10 1.00671L6.00671 5L10 8.99331L8.99331 10L5 6.00671L1.00671 10L0 8.99331L3.99329 5L0 1.00671L1.00671 0L5 3.99329L8.99331 0L10 1.00671Z"/>
				</svg>
			</span>
                <input
                    type="hidden"
                    :id="currentId"
                    :name="name"
                    :value="currentValue"
                    ref="colorpicker"
                >
            </div>
            <div class="cx-vui-colorpicker__dropdown" v-if="panelActive">
                <chrome-picker
                    @input="setCurrentValue"
                    v-model="currentValue"
                ></chrome-picker>
            </div>
        </div>
    </cx-vui-component-wrapper>
</script>
<script type="text/x-template" id="cx-vui-component-wrapper">
    <div
        :class="wrapperClasses()" v-if="isVisible() && ! preventWrap"
    >
        <div
            class="cx-vui-component__meta"
            v-if="this.label || this.description"
        >
            <label
                class="cx-vui-component__label"
                v-if="this.label"
                :for="elementId"
                v-html="this.label"
            ></label>
            <div
                class="cx-vui-component__desc"
                v-if="this.description"
                v-html="this.description"
            ></div>
        </div>
        <div class="cx-vui-component__control">
            <slot></slot>
        </div>
    </div>
    <div
        :class="wrapperClassesRaw"
        v-else-if="isVisible()"
    >
        <slot></slot>
    </div>
</script>
<script type="text/x-template" id="cx-vui-dimensions">
    <cx-vui-component-wrapper
        :elementId="currentId"
        :label="label"
        :description="description"
        :wrapper-css="wrapperCss"
        :preventWrap="preventWrap"
        v-if="isVisible()"
    >
        <div
            :class="controlClasses"
        >
            <div class="cx-vui-dimensions__units">
			<span
                v-for="unit in units"
                :class="{ active: unit.unit === currentValue['units'] }"
                @click="unitHandler(unit.unit)"
            >{{ unit.unit }}</span>
            </div>
            <div class="cx-vui-dimensions__inputs">
                <cx-vui-input
                    :name="`${name}-top`"
                    size="fullwidth"
                    :wrapper-css="[ 'equalwidth' ]"
                    type="number"
                    :min="min"
                    :max="max"
                    :step="step"
                    :prevent-wrap="true"
                    placeholder="Top"
                    @input="handleInput"
                    @on-change="handleChange"
                    v-model="currentValue['top']"
                >
                </cx-vui-input>
                <cx-vui-input
                    :name="`${name}-right`"
                    size="fullwidth"
                    :wrapper-css="[ 'equalwidth' ]"
                    type="number"
                    :min="min"
                    :max="max"
                    :step="step"
                    :prevent-wrap="true"
                    placeholder="Right"
                    @input="handleInput"
                    @on-change="handleChange"
                    v-model="currentValue['right']"
                >
                </cx-vui-input>
                <cx-vui-input
                    :name="`${name}-bottom`"
                    size="fullwidth"
                    :wrapper-css="[ 'equalwidth' ]"
                    type="number"
                    :min="min"
                    :max="max"
                    :step="step"
                    :prevent-wrap="true"
                    placeholder="Bottom"
                    @input="handleInput"
                    @on-change="handleChange"
                    v-model="currentValue['bottom']"
                >
                </cx-vui-input>
                <cx-vui-input
                    :name="`${name}-left`"
                    size="fullwidth"
                    :wrapper-css="[ 'equalwidth' ]"
                    type="number"
                    :min="min"
                    :max="max"
                    :step="step"
                    :prevent-wrap="true"
                    placeholder="Left"
                    @input="handleInput"
                    @on-change="handleChange"
                    v-model="currentValue['left']"
                >
                </cx-vui-input>
                <div
                    class="cx-vui-dimensions__link"
                    @click="linkHandler"
                >
                    <svg v-if="isLink" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M10.2061 5.79298C12.0732 7.66201 12.0476 10.6585 10.2174 12.4989C10.2139 12.5027 10.2099 12.5067 10.2061 12.5105L8.10613 14.6105C6.25395 16.4627 3.24054 16.4624 1.38864 14.6105C-0.463547 12.7586 -0.463547 9.74485 1.38864 7.89298L2.5482 6.73342C2.8557 6.42592 3.38526 6.63029 3.40114 7.06485C3.42139 7.61867 3.5207 8.17507 3.70395 8.71238C3.76601 8.89432 3.72167 9.09557 3.58573 9.23151L3.17676 9.64048C2.30095 10.5163 2.27348 11.9424 3.14067 12.8267C4.01642 13.7198 5.45585 13.7251 6.33832 12.8427L8.43832 10.743C9.31929 9.86201 9.3156 8.43807 8.43832 7.56079C8.32266 7.44535 8.20616 7.35567 8.11516 7.29301C8.05079 7.2488 7.99763 7.19016 7.95994 7.12177C7.92225 7.05337 7.90106 6.97711 7.89807 6.89907C7.8857 6.56885 8.0027 6.22857 8.26363 5.96764L8.92157 5.30967C9.0941 5.13714 9.36476 5.11595 9.56482 5.25557C9.79393 5.41556 10.0085 5.59539 10.2061 5.79298V5.79298ZM14.6103 1.38855C12.7584 -0.46339 9.74504 -0.46364 7.89285 1.38855L5.79285 3.48854C5.7891 3.49229 5.78504 3.49636 5.7816 3.50011C3.95142 5.34048 3.92576 8.33701 5.79285 10.206C5.99044 10.4036 6.20503 10.5834 6.43414 10.7434C6.6342 10.883 6.90488 10.8618 7.07738 10.6893L7.73532 10.0314C7.99626 9.77042 8.11326 9.43013 8.10088 9.09992C8.09789 9.02188 8.07671 8.94562 8.03901 8.87722C8.00132 8.80883 7.94817 8.75019 7.88379 8.70598C7.79279 8.64332 7.67629 8.55363 7.56063 8.4382C6.68335 7.56092 6.67967 6.13698 7.56063 5.25601L9.66063 3.15633C10.5431 2.27386 11.9825 2.27917 12.8583 3.17226C13.7255 4.05664 13.698 5.4827 12.8222 6.35851L12.4132 6.76748C12.2773 6.90342 12.2329 7.10467 12.295 7.2866C12.4783 7.82392 12.5776 8.38032 12.5978 8.93413C12.6137 9.3687 13.1433 9.57307 13.4508 9.26557L14.6103 8.10601C16.4625 6.25417 16.4625 3.24042 14.6103 1.38855V1.38855Z" fill="#7B7E81"/>
                    </svg>
                    <svg v-if="!isLink" width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9.50186 12.6839C9.64829 12.8303 9.64829 13.0677 9.50186 13.2142L8.1058 14.6103C6.25383 16.4622 3.24039 16.4623 1.38827 14.6103C-0.463726 12.7582 -0.463726 9.74477 1.38827 7.89277L2.78436 6.49668C2.9308 6.35024 3.16824 6.35024 3.31471 6.49668L4.55214 7.73411C4.69858 7.88055 4.69858 8.11799 4.55214 8.26446L3.15605 9.66052C2.2788 10.5378 2.2788 11.9652 3.15605 12.8425C4.0333 13.7197 5.46074 13.7198 6.33805 12.8425L7.73411 11.4464C7.88055 11.3 8.11798 11.3 8.26445 11.4464L9.50186 12.6839ZM7.73411 4.55211C7.88055 4.69855 8.11798 4.69855 8.26445 4.55211L9.66051 3.15605C10.5378 2.27871 11.9652 2.27877 12.8425 3.15605C13.7198 4.03333 13.7198 5.46074 12.8425 6.33802L11.4464 7.73408C11.3 7.88052 11.3 8.11796 11.4464 8.26442L12.6839 9.50186C12.8303 9.6483 13.0677 9.6483 13.2142 9.50186L14.6103 8.10577C16.4623 6.25374 16.4623 3.2403 14.6103 1.38827C12.7582 -0.463724 9.74482 -0.463724 7.89276 1.38827L6.4967 2.78434C6.35027 2.93077 6.35027 3.16821 6.4967 3.31468L7.73411 4.55211ZM15.0725 15.7796L15.7796 15.0725C16.0725 14.7796 16.0725 14.3047 15.7796 14.0119L1.98671 0.218932C1.6938 -0.0739742 1.21893 -0.0739742 0.926053 0.218932L0.218929 0.926056C-0.0739768 1.21896 -0.0739768 1.69384 0.218929 1.98671L14.0118 15.7796C14.3047 16.0725 14.7796 16.0725 15.0725 15.7796V15.7796Z" fill="#7B7E81"/>
                    </svg>
                </div>
            </div>
        </div>
    </cx-vui-component-wrapper>
</script>
<script type="text/x-template" id="cx-vui-f-select">
    <cx-vui-component-wrapper
        :elementId="currentId"
        :label="label"
        :description="description"
        :wrapper-css="wrapperCss"
        :preventWrap="preventWrap"
        v-if="isVisible()"
    >
        <div class="cx-vui-f-select">
            <div :class="{
			'cx-vui-f-select__selected': true,
			'cx-vui-f-select__selected-not-empty': this.currentValues.length > 0
		}">
                <div
                    v-for="option in selectedOptions"
                    class="cx-vui-f-select__selected-option"
                    @click="handleResultClick( option.value )"
                >
				<span class="cx-vui-f-select__selected-option-icon">
					<svg width="10" height="10" viewBox="0 0 10 10" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10 1.00671L6.00671 5L10 8.99329L8.99329 10L5 6.00671L1.00671 10L0 8.99329L3.99329 5L0 1.00671L1.00671 0L5 3.99329L8.99329 0L10 1.00671Z"/></svg>
				</span>
                    {{ option.label }}
                </div>
            </div>
            <div
                v-click-outside.capture="onClickOutside"
                v-click-outside:mousedown.capture="onClickOutside"
                v-click-outside:touchstart.capture="onClickOutside"

                @keydown.up.prevent="handleOptionsNav"
                @keydown.down.prevent="handleOptionsNav"
                @keydown.tab="handleOptionsNav"
                @keydown.enter="handleEnter"

                class="cx-vui-f-select__control"
            >
                <input
                    :id="currentId"
                    :placeholder="placeholder"
                    :autocomplete="autocomplete"
                    type="text"
                    :value="query"
                    @input="handleInput"
                    @focus="handleFocus"
                    :class="{
					'cx-vui-f-select__input': true,
					'cx-vui-input--in-focus': this.inFocus,
					'cx-vui-input': true,
					'size-fullwidth': true,
					'has-error': error,
				}"
                >
                <div class="cx-vui-f-select__results" v-if="inFocus">
                    <div v-if="remote && loading" class="cx-vui-f-select__results-loading" v-html="loadingMessage"></div>
                    <div
                        v-else-if="remote && charsDiff > 0"
                        v-html="parsedRemoteTriggerMessage"
                        class="cx-vui-f-select__results-message"
                    ></div>
                    <div
                        v-else-if="! filteredOptions.length"
                        v-html="notFoundMeassge"
                        class="cx-vui-f-select__results-message"
                    ></div>
                    <div v-else>

                        <div
                            v-for="( option, optionIndex ) in filteredOptions"
                            :class="{
							'cx-vui-f-select__result': true,
							'in-focus': optionIndex === optionInFocus,
							'is-selected': isOptionSelected( option )
						}"
                            @click="handleResultClick( option.value )"
                        >{{ option.label }}</div>
                    </div>
                </div>
            </div>
            <select
                :placeholder="placeholder"
                :disabled="disabled"
                :readonly="readonly"
                :name="name"
                :multiple="multiple"
                :value="currentValues"
                class="cx-vui-f-select__select-tag"
            >
                <option
                    v-for="option in currentValues"
                    :value="option"
                    selected
                ></option>
            </select>
        </div>
        <slot></slot>
    </cx-vui-component-wrapper>
</script>
<script type="text/x-template" id="cx-vui-fselect"></script>
<script type="text/x-template" id="cx-vui-iconpicker">
    <cx-vui-component-wrapper
        :elementId="currentId"
        :label="label"
        :description="description"
        :wrapper-css="wrapperCss"
        :preventWrap="preventWrap"
        v-if="isVisible()"
    >
        <div
            class="cx-vui-iconpicker"
            v-click-outside.capture="onClickOutside"
            v-click-outside:mousedown.capture="onClickOutside"
            v-click-outside:touchstart.capture="onClickOutside"
        >
            <div class="cx-vui-iconpicker__fieldgroup">
                <div
                    class="cx-vui-iconpicker__preview"
                    @click="panelActive = true"
                >
                    <i :class="[ iconBase, currentValue ]"></i>
                </div>
                <input
                    :id="currentId"
                    :class="[ 'cx-vui-iconpicker__input', 'cx-vui-input' ]"
                    :placeholder="placeholder"
                    :disabled="disabled"
                    :autocomplete="autocomplete"
                    :readonly="readonly"
                    :name="name"
                    :value="currentValue"
                    :autofocus="autofocus"
                    @keyup.enter="handleEnter"
                    @keyup.esc="handleEsc"
                    @focus="handleFocus"
                    @blur="handleBlur"
                    @input="handleInput"
                    @change="handleChange"
                    ref="iconpicker"
                >
            </div>
            <div class="cx-vui-iconpicker__canvas" v-if="panelActive">
                <div class="cx-vui-iconpicker__canvas-content">
                    <div class="cx-vui-iconpicker__canvas-header">
                        <input
                            type="text"
                            class="cx-vui-iconpicker__canvas-search cx-vui-input size-fullwidth"
                            v-model="filterQuery"
                        >
                    </div>
                    <div class="cx-vui-iconpicker__canvas-list">
                        <div
                            tabindex="0"
                            v-for="icon in filteredIcons"
                            :class="{
							'cx-vui-iconpicker__canvas-icon': true,
							'cx-vui-iconpicker__canvas-icon--selected': icon === currentValue,
						}"
                            @click="seclectIcon( icon )"
                            @keyup.enter="seclectIcon( icon )"
                            @keyup.esc="closePanel()"
                        >
                            <i :class="[ iconBase, icon ]"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </cx-vui-component-wrapper>
</script>
<script type="text/x-template" id="cx-vui-input">
    <cx-vui-component-wrapper
        :elementId="currentId"
        :label="label"
        :description="description"
        :wrapper-css="wrapperCss"
        :preventWrap="preventWrap"
        v-if="isVisible()"
    >
        <input
            :id="currentId"
            :type="type"
            :class="controlClasses"
            :placeholder="placeholder"
            :disabled="disabled"
            :maxlength="maxlength"
            :readonly="readonly"
            :name="name"
            :value="currentValue"
            :autofocus="autofocus"
            :min="min"
            :max="max"
            :step="step"
            @keyup.enter="handleEnter"
            @keyup="handleKeyup"
            @keypress="handleKeypress"
            @keydown="handleKeydown"
            @focus="handleFocus"
            @blur="handleBlur"
            @input="handleInput"
            @change="handleChange"
        >
        <slot></slot>
    </cx-vui-component-wrapper>
</script>
<script type="text/x-template" id="cx-vui-list-table">
    <div class="cx-vue-list-table">
        <div class="cx-vue-list-table__heading">
            <slot name="heading"></slot>
        </div>
        <div
            class="cx-vue-list-table__items"
            v-if="! isEmpty"
        >
            <slot name="items"></slot>
        </div>
        <div
            class="cx-vue-list-table__items is-empty"
            v-else
            v-html="emptyMessage"
        ></div>
        <div class="cx-vue-list-table__footer">
            <slot name="footer"></slot>
        </div>
    </div>
</script>
<script type="text/x-template" id="cx-vui-list-table-heading">
    <div :class="[
	'list-table-heading',
	className
]">
        <div
            :class="[
			'list-table-heading__cell',
			'cell--' + slot
		]"
            v-for="slot in slots"
        >
            <slot :name="slot"></slot>
        </div>
    </div>
</script>
<script type="text/x-template" id="cx-vui-list-table-item">
    <div :class="[
	'list-table-item',
	className
]">
        <div
            :class="[
			'list-table-item__cell',
			'cell--' + slot
		]"
            v-for="slot in slots"
        >
            <slot :name="slot"></slot>
        </div>
    </div>
</script>
<script type="text/x-template" id="cx-vui-notice">
    <div class="cx-vui-notices">
        <transition-group name="cx-vui-notices" tag="div">
            <div
                v-for="( item, itemID ) in stack"
                :class="[
				'cx-vui-notice',
				'cx-vui-notice--' + item.type,
			]"
                :key="'item-' + itemID"
            >
                <div
                    :class="[
					'cx-vui-notice__icon',
					'cx-vui-notice__icon--' + item.type,
				]"
                    v-html="getIcon( item.type )"
                ></div>
                <div class="cx-vui-notice__content">
                    <div
                        class="cx-vui-notice__title"
                        v-if="item.title"
                        v-html="item.title"
                    ></div>
                    <div
                        class="cx-vui-notice__message"
                        v-if="item.message"
                        v-html="item.message"
                    ></div>
                </div>
                <div
                    class="cx-vui-notice__close"
                    @click="destroyItem( itemID )"
                >
                    <svg viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M16 1.61074L9.61074 8L16 14.3893L14.3893 16L8 9.61074L1.61074 16L0 14.3893L6.38926 8L0 1.61074L1.61074 0L8 6.38926L14.3893 0L16 1.61074Z"/></svg>
                </div>
            </div>
        </transition-group>
    </div>
</script>
<script type="text/x-template" id="cx-vui-pagination">
    <div
        :class="classesList"
    >
        <ul :class="[baseClass + '-items']">
            <li
                :class="prevClasses"
                @click="prev">
                <svg width="7" height="12" viewBox="0 0 7 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M5.67089 0L-5.96046e-08 6L5.67089 12L7 10.5938L2.65823 6L7 1.40625L5.67089 0Z" fill="#7B7E81"/>
                </svg>
            </li>
            <li :class="firstPageClasses" @click="changePage(1)"><span>1</span></li>
            <li v-if="currentPage > 5" :class="[baseClass + '-item', baseClass + '-item-jump-prev']" @click="fastPrev"><span>...</span></li>
            <li v-if="currentPage === 5" :class="[baseClass + '-item']" @click="changePage(currentPage - 3)"><span>{{ currentPage - 3 }}</span></li>
            <li v-if="currentPage - 2 > 1" :class="[baseClass + '-item']" @click="changePage(currentPage - 2)"><span>{{ currentPage - 2 }}</span></li>
            <li v-if="currentPage - 1 > 1" :class="[baseClass + '-item']" @click="changePage(currentPage - 1)"><span>{{ currentPage - 1 }}</span></li>
            <li v-if="currentPage != 1 && currentPage != allPages" :class="[baseClass + '-item',baseClass + '-item-active']"><span>{{ currentPage }}</span></li>
            <li v-if="currentPage + 1 < allPages" :class="[baseClass + '-item']" @click="changePage(currentPage + 1)"><span>{{ currentPage + 1 }}</span></li>
            <li v-if="currentPage + 2 < allPages" :class="[baseClass + '-item']" @click="changePage(currentPage + 2)"><span>{{ currentPage + 2 }}</span></li>
            <li v-if="allPages - currentPage === 4" :class="[baseClass + '-item']" @click="changePage(currentPage + 3)"><span>{{ currentPage + 3 }}</span></li>
            <li v-if="allPages - currentPage >= 5" :class="[baseClass + '-item', baseClass + '-item-jump-next']" @click="fastNext"><span>...</span></li>
            <li v-if="allPages > 1" :class="lastPageClasses" @click="changePage(allPages)"><span>{{ allPages }}</span></li>
            <li
                :class="nextClasses"
                @click="next">
                <svg width="7" height="12" viewBox="0 0 7 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M1.32911 0L7 6L1.32911 12L0 10.5938L4.34177 6L0 1.40625L1.32911 0Z" fill="#7B7E81"/>
                </svg>
            </li>
        </ul>
    </div>
</script>
<script type="text/x-template" id="cx-vui-popup">
    <div class="cx-vui-popup" v-if="value">
        <div
            class="cx-vui-popup__overlay"
            v-if="overlay"
            @click="handleCancel"
        ></div>
        <div
            class="cx-vui-popup__body"
            :style="'width:' + bodyWidth"
        >
            <div
                class="cx-vui-popup__close"
                v-if="close"
                @click="handleCancel"
            >
                <svg width="20" height="20" viewBox="0 0 14 14" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 3.00671L8.00671 7L12 10.9933L10.9933 12L7 8.00671L3.00671 12L2 10.9933L5.99329 7L2 3.00671L3.00671 2L7 5.99329L10.9933 2L12 3.00671Z"/></svg>
            </div>
            <div class="cx-vui-popup__header" v-if="header">
                <slot name="title"></slot>
            </div>
            <div class="cx-vui-popup__content">
                <slot name="content"></slot>
            </div>
            <div class="cx-vui-popup__footer" v-if="footer">
                <cx-vui-button
                    :button-style="'accent'"
                    :size="'mini'"
                    @click="handleOk"
                    v-if="showOk"
                ><span slot="label">{{ okLabel }}</span></cx-vui-button>
                <cx-vui-button
                    :size="'mini'"
                    @click="handleCancel"
                    v-if="showCancel"
                ><span slot="label">{{ cancelLabel }}</span></cx-vui-button>
            </div>
        </div>
    </div>
</script>
<script type="text/x-template" id="cx-vui-radio">
    <cx-vui-component-wrapper
        :elementId="currentId"
        :label="label"
        :description="description"
        :wrapper-css="wrapperCss"
        :preventWrap="preventWrap"
        v-if="isVisible()"
    >
        <div
            :class="{
			'cx-vui-radiogroup': true,
			'cx-vui-radiogroup--single-item': this.optionsList.length === 1
		}"
            :id="currentId"
            tabindex="0"
        >
            <div
                class="cx-vui-radio-wrap"
                v-for="( option, index ) in optionsList"
            >
                <div
                    :key="name + option.value + index"
                    :class="{
					'cx-vui-radio': true,
					'cx-vui-radio--disabled': disabled,
					'cx-vui-radio--checked': option.value === currentValue,
					'cx-vui-radio--focused': isOptionInFocus( option.value ),
				}"
                    @click="handleInput( $event, option.value )"
                    @focus="handleFocus( $event, option.value )"
                    @blur="handleBlur( $event, option.value )"
                    tabindex="0"
                >
                    <input
                        :class="{
						'cx-vui-radio__input': true,
					}"
                        :name="name + '[' + option.value + ']'"
                        :value="option.value"
                        :checked="option.value === currentValue"
                        :disabled="disabled"
                        type="radio"
                    >
                    <div
                        :class="{
						'cx-vui-radio__mark': true,
						'cx-vui-radio__mark--disabled': disabled,
						'cx-vui-radio__mark--checked': option.value === currentValue,
						'cx-vui-radio__mark--focused': isOptionInFocus( option.value ),
					}"
                    >
                    </div>
                    <div
                        :class="{
						'cx-vui-radio__label': true,
					}"
                        v-html="option.label"
                    ></div>
                </div>
            </div>
        </div>
    </cx-vui-component-wrapper>
</script>
<script type="text/x-template" id="cx-vui-repeater">
    <div
        class="cx-vui-repeater"
        tabindex="0"
        @focus="inFocus = true"
        @blur="inFocus = false"
        @keyup.alt.n.stop="handleClick"
        v-if="isVisible()"
    >
        <div class="cx-vui-repeater__items">
            <slot></slot>
        </div>
        <div class="cx-vui-repeater__actions">
            <cx-vui-button
                :button-style="buttonStyle"
                :size="buttonSize"
                @click="handleClick"
            ><span slot="label">{{ buttonLabel }}</span></cx-vui-button>
            <i class="cx-vui-repeater__tip" v-if="inFocus">
                Or press <kbd>alt</kbd>+<kbd>n</kbd> to add new item
            </i>
        </div>
    </div>
</script>
<script type="text/x-template" id="cx-vui-repeater-item">
    <div
        :class="[
		{
			'cx-vui-repeater-item': true,
			'cx-vui-panel': true,
			'cx-vui-repeater-item--is-collpased': this.isCollapsed,
		},
		this.customCssClass
	]"
    >
        <div
            :class="{
			'cx-vui-repeater-item__heading': true,
			'cx-vui-repeater-item__heading--is-collpased': this.isCollapsed
		}"
        >
            <div
                class="cx-vui-repeater-item__heading-start"
                @click.stop="isCollapsed = ! isCollapsed"
            >
                <div
                    v-handle
                    class="cx-vui-repeater-item__handle"
                >
                    <svg width="16" height="14" viewBox="0 0 16 14" fill="none" xmlns="http://www.w3.org/2000/svg"><line x1="2" y1="3" x2="14" y2="3" stroke-width="2"/><line x1="2" y1="11" x2="14" y2="11" stroke-width="2"/><line x1="2" y1="7" x2="14" y2="7" stroke-width="2"/></svg>
                </div>
                <svg
                    :class="{
					'cx-vui-repeater-item__collapse':true,
					'cx-vui-repeater-item__collapse--is-collpased': this.isCollapsed
				}"
                    width="14"
                    height="14"
                    viewBox="0 0 14 14"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                ><rect width="14" height="14" transform="matrix(1 0 0 -1 0 14)" fill="white"/><path d="M13 5.32911L7 11L1 5.32911L2.40625 4L7 8.34177L11.5938 4L13 5.32911Z"/></svg>
                <div class="cx-vui-repeater-item__title" v-html="title"></div>
                <div class="cx-vui-repeater-item__subtitle" v-html="subtitle"></div>
            </div>
            <div class="cx-vui-repeater-item__heading-end">
                <div
                    class="cx-vui-repeater-item__copy"
                    @click="handleCopy"
                >
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="16" height="16" transform="matrix(1 0 0 -1 0 16)" fill="white"/><path d="M13.401 14.5362V4.35745H5.47721V14.5362H13.401ZM13.401 2.92766C13.7848 2.92766 14.1234 3.07518 14.4169 3.37021C14.7104 3.64255 14.8571 3.97163 14.8571 4.35745V14.5362C14.8571 14.922 14.7104 15.2624 14.4169 15.5574C14.1234 15.8525 13.7848 16 13.401 16H5.47721C5.09344 16 4.75482 15.8525 4.46134 15.5574C4.16787 15.2624 4.02113 14.922 4.02113 14.5362V4.35745C4.02113 3.97163 4.16787 3.64255 4.46134 3.37021C4.75482 3.07518 5.09344 2.92766 5.47721 2.92766H13.401ZM11.2338 0V1.46383H2.56504V11.6426H1.14282V1.46383C1.14282 1.07801 1.27827 0.737589 1.54917 0.442553C1.84265 0.147518 2.18127 0 2.56504 0H11.2338Z"/></svg>
                </div>
                <div
                    class="cx-vui-repeater-item__clean"
                    @click="handleDelete"
                >
                    <svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="16" height="16" transform="matrix(1 0 0 -1 0 16)" fill="white"/><path d="M2.28564 14.192V3.42847H13.7142V14.192C13.7142 14.6685 13.5208 15.0889 13.1339 15.4533C12.747 15.8177 12.3005 15.9999 11.7946 15.9999H4.20529C3.69934 15.9999 3.25291 15.8177 2.866 15.4533C2.4791 15.0889 2.28564 14.6685 2.28564 14.192Z"/><path d="M14.8571 1.14286V2.28571H1.14282V1.14286H4.57139L5.56085 0H10.4391L11.4285 1.14286H14.8571Z"/></svg>
                    <div
                        class="cx-vui-tooltip"
                        v-if="showConfirmTip"
                    >
                        Are you sure?
                        <br>
                        <span
                            class="cx-vui-repeater-item__confrim-del"
                            @click.stop="confrimDeletion"
                        >Yes</span>
                        /
                        <span
                            class="cx-vui-repeater-item__cancel-del"
                            @click.stop="cancelDeletion"
                        >No</span>
                    </div>
                </div>
            </div>
        </div>
        <div
            :class="{
			'cx-vui-repeater-item__content': true,
			'cx-vui-repeater-item__content--is-collpased': this.isCollapsed
		}"
        >
            <slot></slot>
        </div>
    </div>
</script>
<script type="text/x-template" id="cx-vui-select">
    <cx-vui-component-wrapper
        :elementId="currentId"
        :label="label"
        :description="description"
        :wrapper-css="wrapperCss"
        :preventWrap="preventWrap"
        v-if="isVisible()"
    >
        <select
            :id="currentId"
            :placeholder="placeholder"
            :disabled="disabled"
            :readonly="readonly"
            :name="name"
            :multiple="multiple"
            v-model="currentValue"
            :class="controlClasses()"
            @focus="handleFocus"
            @blur="handleBlur"
            @change="handleInput"
        >
            <option
                v-if="placeholder"
                value=""
                disabled
            >{{ placeholder }}</option>
            <template v-if="groups.length">
                <optgroup v-for="group in groups" :label="group.label">
                    <option
                        v-for="option in group.options"
                        :value="option.value"
                        :selected="isOptionSelected( option )"
                    >{{ option.label }}</option>
                </optgroup>
            </template>
            <template v-else>
                <option
                    v-for="option in options"
                    :value="option.value"
                    :selected="isOptionSelected( option )"
                >{{ option.label }}</option>
            </template>
        </select>
        <slot></slot>
    </cx-vui-component-wrapper>
</script>
<script type="text/x-template" id="cx-vui-switcher">
    <cx-vui-component-wrapper
        :elementId="currentId"
        :label="label"
        :description="description"
        :wrapper-css="wrapperCss"
        :preventWrap="preventWrap"
        v-if="isVisible()"
    >
        <div
            tabIndex="0"
            :id="currentId"
            :class="{
			'cx-vui-switcher': true,
			'cx-vui-switcher--disabled': this.disabled,
			'cx-vui-switcher--on': this.isOn,
			'cx-vui-switcher--off': ! this.isOn,
			'cx-vui-switcher--in-focus': this.inFocus
		}"
            @keyup.enter="handleEnter"
            @focus="handleFocus"
            @blur="handleBlur"
            @click="switchState"
        >
            <input
                type="hidden"
                checked="checked"
                class="cx-vui-switcher__input"
                :name="name"
                :value="currentValue"
            >
            <div class="cx-vui-switcher__panel"></div>
            <div class="cx-vui-switcher__trigger"></div>
        </div>
    </cx-vui-component-wrapper>
</script>
<script type="text/x-template" id="cx-vui-tabs">
    <div
        :class="{
		'cx-vui-tabs': true,
		'cx-vui-tabs--invert': invert,
		'cx-vui-tabs--layout-vertical': 'vertical' === this.layout,
		'cx-vui-tabs--layout-horizontal': 'horizontal' === this.layout,
		'cx-vui-tabs--in-panel': inPanel,
	}"
    >
        <div class="cx-vui-tabs__nav">
            <div
                v-for="item in navList"
                :class="{
				'cx-vui-tabs__nav-item': true,
				'cx-vui-tabs__nav-item--active': isActive( item.name ),
			}"
                @click="onTabClick( item.name )"
            >
                {{ item.label }}
            </div>
        </div>
        <div class="cx-vui-tabs__content">
            <slot></slot>
        </div>
    </div>
</script>
<script type="text/x-template" id="cx-vui-tabs-panel">
    <div class="cx-vui-tabs-panel" v-show="show">
        <slot></slot>
    </div>
</script>
<script type="text/x-template" id="cx-vui-textarea">
    <cx-vui-component-wrapper
        :elementId="currentId"
        :label="label"
        :description="description"
        :wrapper-css="wrapperCss"
        :preventWrap="preventWrap"
        v-if="isVisible()"
    >
	<textarea
        :id="currentId"
        :class="controlClasses"
        :placeholder="placeholder"
        :disabled="disabled"
        :rows="rows"
        :readonly="readonly"
        :name="name"
        :value="currentValue"
        @keyup.enter="handleEnter"
        @keyup="handleKeyup"
        @keypress="handleKeypress"
        @keydown="handleKeydown"
        @focus="handleFocus"
        @blur="handleBlur"
        @input="handleInput"
        @change="handleChange"
    ></textarea>
        <slot></slot>
    </cx-vui-component-wrapper>
</script>
<script type="text/x-template" id="cx-vui-time">
    <cx-vui-component-wrapper
        :elementId="currentId"
        :label="label"
        :description="description"
        :wrapper-css="wrapperCss"
        :preventWrap="preventWrap"
        v-if="isVisible()"
    >
	<span :class="controlClasses">
		<vue-timepicker
            v-model="currentValue"
            @keyup.enter="handleEnter"
            @keyup="handleKeyup"
            @keypress="handleKeypress"
            @keydown="handleKeydown"
            @focus="handleFocus"
            @blur="handleBlur"
            @input="handleInput"
            @change="handleChange"
            @open="handleOpen"
            @close="handleClose"
            @error="handleError"

            :id="currentId"
            :input-class="controlClasses"
            :name="name"
            :placeholder="placeholder"
            :autocomplete="autocomplete"
            :size="size"
            :autofocus="autofocus"
            :format="format"
            :minute-interval="minuteInterval"
            :second-interval="secondInterval"
            :hide-clear-button="hideClearButton"
            :disabled="disabled"
            :close-on-complete="closeOnComplete"
            :auto-scroll="autoScroll"
            :hour-range="hourRange"
            :minute-range="minuteRange"
            :second-range="secondRange"
            :hide-disabled-hours="hideDisabledHours"
            :hide-disabled-minutes="hideDisabledMinutes"
            :hide-disabled-seconds="hideDisabledSeconds"
            :hide-disabled-items="hideDisabledItems"
            :advanced-keyboard="advancedKeyboard"
            :blur-delay="blurDelay"
            :manual-input="manualDnput"
            :manual-input-timeout="manualInputTimeout"
            :hide-dropdown="hideDropdown"
            :fixed-dropdown-button="fixedDropdownButton"
            :drop-direction="dropDirection"
            :container-id="containerId"
            :drop-offset-height="dropOffsetHeight"
            :lazy="lazy"
            :debug-mode="debugMode"
        ></vue-timepicker>
	</span>
    </cx-vui-component-wrapper>
</script>
<script type="text/x-template" id="cx-vui-title">
    <h2 class="cx-vui-title"></h2>
</script>
<script type="text/x-template" id="cx-vui-wp-media">
    <cx-vui-component-wrapper
        :elementId="currentId"
        :label="label"
        :description="description"
        :wrapper-css="wrapperCss"
        :preventWrap="preventWrap"
        v-if="isVisible()"
    >
        <div
            :class="controlClasses"
        >

            <SlickList
                class="cx-vui-media__attachments"
                axis="xy"
                :distance="1"
                :hideSortableGhost="false"
                @input="sortInput"
                v-model="attachmentList"
            >
                <SlickItem
                    :class="[ 'cx-vui-media__attachment', `attachment-type-${ attachment.type }` ]"
                    v-for="(attachment, index) in attachmentList"
                    :index="index"
                    :key="index"
                >

                    <svg
                        class="cx-vui-media__control cx-vui-media__control-remove"
                        @click="removeAttachment(attachment.id)"
                        width="16"
                        height="16"
                        viewBox="0 0 16 16"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                    >
                        <path d="M13 4.00671L9.00671 8L13 11.9933L11.9933 13L8 9.00671L4.00671 13L3 11.9933L6.99329 8L3 4.00671L4.00671 3L8 6.99329L11.9933 3L13 4.00671Z" fill="#C92C2C"/>
                    </svg>

                    <img :src="'image'===attachment.type ? attachment.url : attachment.icon" alt="">
                </SlickItem>
            </SlickList>
            <div
                class="cx-vui-media__add-button"
                @click="openWpMediaPopup"
            >
                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M11.75 5.25H6.75V0.25C6.75 0.111875 6.63813 0 6.5 0H5.5C5.36187 0 5.25 0.111875 5.25 0.25V5.25H0.25C0.111875 5.25 0 5.36187 0 5.5V6.5C0 6.63813 0.111875 6.75 0.25 6.75H5.25V11.75C5.25 11.8881 5.36187 12 5.5 12H6.5C6.63813 12 6.75 11.8881 6.75 11.75V6.75H11.75C11.8881 6.75 12 6.63813 12 6.5V5.5C12 5.36187 11.8881 5.25 11.75 5.25Z" fill="#7B7E81"/>
                </svg>
            </div>
            <input
                type="hidden"
                :id="currentId"
                :name="name"
                :value="inputValue"
                ref="wpmedia"
            >
        </div>
    </cx-vui-component-wrapper>
</script>