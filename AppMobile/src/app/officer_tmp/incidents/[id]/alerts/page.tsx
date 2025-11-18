export const metadata = {
  title: "Incident Alerts",
};

export default function Page({ params }: { params: { id: string } }) {
  return (
    <main>
      <h1>Incident Alerts</h1>
      <p>Alerts associated with this incident. Currently viewing record {params.id}.</p>
    </main>
  );
}
