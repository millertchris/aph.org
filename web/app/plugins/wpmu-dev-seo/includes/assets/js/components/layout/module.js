import React from 'react';
import SideBar from '../../components/navigations/side-bar';
import Header from './header';
import ConfigValues from '../../es6/config-values';
import Footer from './footer';
import RequestUtil from '../../utils/request-util';
import { connect } from 'react-redux';

class Module extends React.Component {
	static defaultProps = {
		name: '',
		title: '',
		docChapter: '',
		utmCampaign: '',
		submodules: [],
		onChange: () => false,
	};

	constructor(props) {
		super(props);

		const items = ConfigValues.get('submodules', this.props.name) || [];

		this.state = {
			items,
			statusUpdated: false,
		};
	}

	handleChange(selected) {
		const urlObj = new URL(location.href);
		const searchParams = urlObj.searchParams;

		searchParams.set('tab', selected);
		searchParams.delete('sub');

		const updatedUrl =
			urlObj.origin +
			urlObj.pathname +
			'?' +
			searchParams.toString() +
			urlObj.hash;

		history.replaceState({}, '', updatedUrl);

		const { updateSelected } = this.props;

		updateSelected(selected);
	}

	render() {
		const { title, docChapter, utmCampaign, submodules, selected } =
			this.props;
		const { items } = this.state;

		return (
			<>
				<Header
					title={title}
					docChapter={docChapter}
					utmCampaign={utmCampaign}
				></Header>

				<section className="sui-row-with-sidenav">
					<SideBar
						disabled={selected && this.props[selected]?.loading}
						items={items}
						selected={selected}
						onChange={(tab) => this.handleChange(tab)}
					></SideBar>

					{submodules[selected]}
				</section>

				<Footer />
			</>
		);
	}
}

const mapStateToProps = (state) => ({ ...state });

const mapDispatchToProps = {
	updateSelected: (selected) => ({
		type: 'UPDATE_SELECTED',
		selected,
	}),
};

export default connect(mapStateToProps, mapDispatchToProps)(Module);
