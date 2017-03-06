define(["./ComponentView"],
	function(ComponentView) {

		/**
		 * @class WebFrameView
		 * @augments ComponentView
		 */
		return ComponentView.extend({
			className: "component webFrameView",

			/**
			 * Initialize WebFrameView component view.
			 */
			initialize: function() {
				return ComponentView.prototype.initialize.apply(this, arguments);
			},

      checkXSS: function (url) {
        return url.substr(0, 11) === 'javascript:';
      },

			/**
			 * Render element based on component model.
			 *
			 * @returns {*}
			 */
			render: function() {
				var $frame, scale;
				ComponentView.prototype.render.call(this);
				$frame = $("<iframe width='960' height='768' src=" + (this.checkXSS(this.model.get('src')) ? '' : this.model.get('src')) + "></iframe>");
				this.$el.find(".content").append($frame);
				this.$el.append('<div class="overlay"></div>');
				scale = this.model.get('scale');
				this.$el.css({
					width: 960 * scale.x,
					height: 768 * scale.y
				});
				return this.$el;
			}
		});
	});