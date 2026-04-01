;(function (wp) {
	wp.plugins.registerPlugin('wpmudev-videos', {
		render: function () {
			return wp.element.createElement(
				wp.editPost.PluginDocumentSettingPanel,
				{
					name: 'wpmudev-videos',
					title: window.ivtVars.videos_menu_title,
					icon: 'ivt',
					className: 'wpmudev-videos-tutorials',
				},
				wp.element.createElement(wp.serverSideRender, {
					block: 'ivt/videos',
					className: 'wpmudev-videos-tutorials-content',
				})
			)
		},
	})
})(window.wp)
