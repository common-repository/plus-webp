import './plus-webp.scss';

import { render } from '@wordpress/element';

import Credit from './components/credit';
import Generate from './components/generate';
import Settings from './components/settings';

const PlusWebp = () => {

	return (
		<div className="wrap">
			<h2>Plus WebP or AVIF</h2>
			<Credit />
			<hr />
			<Generate />
			<hr />
			<Settings />
		</div>
	);

};

render(
	<PlusWebp />,
	document.getElementById( 'plus-webp-page' )
);

